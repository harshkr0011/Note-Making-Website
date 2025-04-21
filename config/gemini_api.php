<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function gemini_summarise($text) {
    $api_key = 'AIzaSyDQyJ4G_3sOxGxd6pIuNS_69iLDYI-7Z4w';
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $api_key;

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $text]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return 'Failed to get response from Gemini API: ' . $error;
    }

    $responseData = json_decode($response, true);
    return $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'No response from Gemini.';
}
?>