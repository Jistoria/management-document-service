<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class VerifyJwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'No token provided'], 401);
        }

        try {
            $jwks = cache()->remember('auth-jwks', 3600, function () {
                $response = Http::get(config('auth.jwks_url'));
                return $response->json();
            });

            $keyData = collect($jwks['keys'])->firstWhere('kid', 'passport-v1');
            if (!$keyData) {
                return response()->json(['error' => 'No matching key found'], 401);
            }

            $publicKey = $this->createPemFromModulusAndExponent($keyData['n'], $keyData['e']);

            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

            // Puedes guardar info útil en el request para control de acceso
            $request->attributes->set('jwt_user_id', $decoded->sub ?? null);
            $request->attributes->set('jwt_roles', $decoded->roles ?? []);

            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token', 'message' => $e->getMessage()], 401);
        }
    }

    private function createPemFromModulusAndExponent(string $n, string $e): string
    {
        $mod = base64_decode(strtr($n, '-_', '+/'));
        $exp = base64_decode(strtr($e, '-_', '+/'));

        $components = [
            'modulus' => $mod,
            'publicExponent' => $exp,
        ];

        // Generar PEM (Formato ASN.1 → DER → PEM)
        $rsa = openssl_pkey_get_details(openssl_pkey_get_public(
            "-----BEGIN PUBLIC KEY-----\n" .
                chunk_split(base64_encode(pack('H*', bin2hex($mod))), 64, "\n") .
                "-----END PUBLIC KEY-----"
        ));

        // Usamos OpenSSL internamente
        return "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split(base64_encode($this->encodeRsaPublicKey($mod, $exp)), 64, "\n") .
            "-----END PUBLIC KEY-----";
    }

    // Codificador ASN.1 simple
    private function encodeLength($length)
    {
        if ($length <= 0x7F) return chr($length);
        $temp = ltrim(pack('N', $length), chr(0));
        return chr(0x80 | strlen($temp)) . $temp;
    }

    private function encodeRsaPublicKey($modulus, $exponent)
    {
        $modulus = ltrim($modulus, "\x00"); // Remove leading 0
        $exponent = ltrim($exponent, "\x00");

        $modulusEnc = "\x02" . $this->encodeLength(strlen($modulus)) . $modulus;
        $exponentEnc = "\x02" . $this->encodeLength(strlen($exponent)) . $exponent;

        $sequence = "\x30" . $this->encodeLength(strlen($modulusEnc . $exponentEnc)) . $modulusEnc . $exponentEnc;

        $bitString = "\x03" . $this->encodeLength(strlen($sequence) + 1) . "\x00" . $sequence;

        $sequence2 = "\x30" . $this->encodeLength(strlen($bitString)) . $bitString;

        return $sequence2;
    }
}
