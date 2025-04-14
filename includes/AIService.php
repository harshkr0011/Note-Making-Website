<?php
require_once 'config/database.php';

class AIService {
    private $db;
    private $apiKey;
    private $apiEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Load API key from environment or config
        $this->apiKey = getenv('GEMINI_API_KEY') ?: 'YOUR_API_KEY';
    }

    public function generateSummary($noteId, $content, $length = 'medium') {
        try {
            $prompt = "Please provide a {$length} summary of the following note:\n\n{$content}";
            
            $response = $this->callGeminiAPI($prompt);
            $summary = $response['candidates'][0]['content']['parts'][0]['text'];

            // Save summary to database
            $query = "INSERT INTO note_summaries (note_id, summary) VALUES (:note_id, :summary) 
                     ON DUPLICATE KEY UPDATE summary = :summary";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":note_id", $noteId);
            $stmt->bindParam(":summary", $summary);
            $stmt->execute();

            return $summary;
        } catch (Exception $e) {
            error_log("AI Summary Error: " . $e->getMessage());
            return false;
        }
    }

    public function chat($userId, $message) {
        try {
            // For now, return a simple response
            return "Hello! I received your message: " . htmlspecialchars($message);
            
            // Uncomment this when you have a real API key
            /*
            $data = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $message]
                        ]
                    ]
                ]
            ];

            $ch = curl_init($this->apiEndpoint . '?key=' . $this->apiKey);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new Exception("API request failed with status code: " . $httpCode);
            }

            $result = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response from API");
            }

            return $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not process your request.';
            */
        } catch (Exception $e) {
            error_log("AI Service Error: " . $e->getMessage());
            return "Hello! I received your message: " . htmlspecialchars($message);
        }
    }

    private function callGeminiAPI($prompt) {
        $headers = [
            'Content-Type: application/json',
        ];

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1000,
                'topP' => 0.8,
                'topK' => 40
            ]
        ];

        $url = $this->apiEndpoint . '?key=' . $this->apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function getAISettings($userId) {
        try {
            $query = "SELECT settings FROM ai_settings WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? json_decode($row['settings'], true) : [
                'auto_summarize' => false,
                'summary_length' => 'medium',
                'ai_model' => 'gemini-pro'
            ];
        } catch (Exception $e) {
            error_log("Error getting AI settings: " . $e->getMessage());
            return [
                'auto_summarize' => false,
                'summary_length' => 'medium',
                'ai_model' => 'gemini-pro'
            ];
        }
    }

    public function updateAISettings($userId, $settings) {
        try {
            $query = "INSERT INTO ai_settings (user_id, settings) 
                     VALUES (:user_id, :settings) 
                     ON DUPLICATE KEY UPDATE settings = :settings";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $settingsJson = json_encode($settings);
            $stmt->bindParam(":settings", $settingsJson);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating AI settings: " . $e->getMessage());
            return false;
        }
    }
}
?> 