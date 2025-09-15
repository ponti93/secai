<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private $client;
    private $apiKey;
    private $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY'));
    }

    /**
     * Transcribe audio using OpenAI Whisper
     */
    public function transcribeAudio($audioFile, $language = 'en'): array
    {
        try {
            if (!$this->apiKey) {
                return [
                    'success' => false,
                    'error' => 'OpenAI API key not configured'
                ];
            }

            $response = $this->client->post($this->baseUrl . '/audio/transcriptions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($audioFile->getPathname(), 'r'),
                        'filename' => $audioFile->getClientOriginalName(),
                    ],
                    [
                        'name' => 'model',
                        'contents' => 'whisper-1'
                    ],
                    [
                        'name' => 'language',
                        'contents' => $language
                    ],
                    [
                        'name' => 'response_format',
                        'contents' => 'json'
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['text'])) {
                return [
                    'success' => true,
                    'transcript' => $data['text'],
                    'language' => $language,
                    'model' => 'whisper-1',
                    'provider' => 'openai'
                ];
            }

            return [
                'success' => false,
                'error' => 'No transcription returned from OpenAI'
            ];

        } catch (RequestException $e) {
            Log::error('OpenAI Transcription Error: ' . $e->getMessage());
            
            $errorMessage = 'OpenAI API request failed';
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $errorData = json_decode($response->getBody()->getContents(), true);
                $errorMessage = $errorData['error']['message'] ?? $errorMessage;
            }

            return [
                'success' => false,
                'error' => $errorMessage
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Transcription Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Transcription failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test OpenAI API connection
     */
    public function testConnection(): array
    {
        try {
            if (!$this->apiKey) {
                return [
                    'success' => false,
                    'message' => 'OpenAI API key not configured'
                ];
            }

            $response = $this->client->get($this->baseUrl . '/models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['data'])) {
                return [
                    'success' => true,
                    'message' => 'OpenAI API connection successful',
                    'models' => count($data['data'])
                ];
            }

            return [
                'success' => false,
                'message' => 'OpenAI API connection failed'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'OpenAI API connection failed: ' . $e->getMessage()
            ];
        }
    }
}

