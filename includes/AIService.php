<?php
require_once 'config/database.php';

class AIService {
    private $db;
    private $apiKey;
    // Update to the correct Gemini endpoint
    private $apiEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->apiKey = 'AIzaSyDQyJ4G_3sOxGxd6pIuNS_69iLDYI-7Z4w';
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
            $response = $this->callGeminiAPI($message);
            $aiResponse = $response['candidates'][0]['content']['parts'][0]['text'];

            // Save chat history
            $query = "INSERT INTO ai_chat_history (user_id, message, response) 
                     VALUES (:user_id, :message, :response)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":message", $message);
            $stmt->bindParam(":response", $aiResponse);
            $stmt->execute();

            return $aiResponse;
        } catch (Exception $e) {
            error_log("AI Chat Error: " . $e->getMessage());
            return "I'm sorry, I encountered an error. Please try again later.";
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
        $query = "SELECT * FROM ai_settings WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateAISettings($userId, $settings) {
        $query = "INSERT INTO ai_settings (user_id, auto_summarize, summary_length, ai_model) 
                 VALUES (:user_id, :auto_summarize, :summary_length, :ai_model)
                 ON DUPLICATE KEY UPDATE 
                 auto_summarize = :auto_summarize,
                 summary_length = :summary_length,
                 ai_model = :ai_model";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":auto_summarize", $settings['auto_summarize']);
        $stmt->bindParam(":summary_length", $settings['summary_length']);
        $stmt->bindParam(":ai_model", $settings['ai_model']);
        
        return $stmt->execute();
    }
}
?>