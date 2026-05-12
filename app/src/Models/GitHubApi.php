<?php
declare(strict_types=1);

namespace Models;

class GitHubApi
{
    private string $token;
    private string $apiUrl = 'https://api.github.com';
    
    public function __construct(string $token)
    {
        $this->token = $token;
    }
    
    private function request(string $method, string $endpoint, ?array $data = null): array
    {
        $ch = curl_init($this->apiUrl . $endpoint);
        
        $headers = [
            'Authorization: token ' . $this->token,
            'User-Agent: Git-Buddy/1.0',
            'Accept: application/vnd.github.v3+json'
        ];
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // <<< DESACTIVAR SSL (solo local)
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'status_code' => $httpCode,
            'data' => json_decode($response, true),
            'token_expired' => ($httpCode === 401 || $httpCode === 403)
        ];
    }
    
    public function getUser(): array
    {
        return $this->request('GET', '/user');
    }
    
    public function listRepos(): array
    {
        return $this->request('GET', '/user/repos?sort=updated&per_page=50');
    }
    
    public function createRepo(string $name, string $description = '', bool $private = false): array
    {
        $data = [
            'name' => $name,
            'description' => $description,
            'private' => $private,
            'auto_init' => false
        ];
        
        return $this->request('POST', '/user/repos', $data);
    }
}