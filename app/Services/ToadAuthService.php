<?php

namespace App\Services;

use App\Services\Traits\HasToadToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service d'authentification via l'API Toad.
 * Vérifie les identifiants d'un membre du personnel sur l'endpoint /staffs/verify.
 */
class ToadAuthService
{
    use HasToadToken;

    private ?string $token;

    public function __construct()
    {
        $this->token = config('services.toad.token');
    }

    /**
     * Vérifie les identifiants auprès de l'API Toad.
     *
     * @param  string     $email
     * @param  string     $password
     * @return array|null Données du staff + token JWT, null si échec
     */
    public function verify(string $email, string $password): ?array
    {
        $url  = $this->getBaseUrl() . '/staffs/verify';
        $body = ['email' => $email, 'password' => $password];

        try {
            Log::info('Appel Toad /verify', [
                'url'        => $url,
                'with_token' => !empty($this->token),
                'token'      => $this->token,
                'body'       => $body,
            ]);

            $request = Http::acceptJson()->timeout(5);

            if (!empty($this->token)) {
                $request = $request->withToken($this->token, 'Bearer');
            }

            $response     = $request->post($url, $body);
            $status       = $response->status();
            $responseBody = $response->json();

            Log::info('Réponse /verify', ['status' => $status, 'body' => $responseBody]);

            if ($response->successful()) {
                return $responseBody;
            }

            Log::warning('Verify KO', ['status' => $status, 'body' => $responseBody]);
            return null;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Erreur de connexion API Toad', ['msg' => $e->getMessage()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Toad', ['msg' => $e->getMessage()]);
            return null;
        }
    }
}
