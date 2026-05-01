<?php

error_log("MODERATION API CALLED");
function moderate_meme_api($image_path, $caption){

    // If no image, skip moderation
    if(empty($image_path)){
        return [
            'ok' => false,
            'recommended_action' => 'ALLOW'
        ];
    }

    if(!file_exists($image_path)){
        return [
            'ok' => false,
            'recommended_action' => 'ALLOW'
        ];
    }

    $url = "http://127.0.0.1:8000/cyberbullying/moderate_meme";

    $postFields = [
        'caption' => $caption,
        'image'   => new CURLFile($image_path)
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);

    if($response === false){
        curl_close($ch);
        return ['ok'=>false];
    }

    curl_close($ch);

    $decoded = json_decode($response, true);

    error_log("AI RESULT: " . print_r($decoded, true));

    if(!$decoded){
        return ['ok'=>false];
    }

    return [
        'ok' => true,
        'recommended_action' => $decoded['recommended_action'] ?? 'ALLOW',
        'reason' => $decoded['reason'] ?? '',
        'severity' => $decoded['severity'] ?? '',
        'evidence' => $decoded['evidence'] ?? []
    
    ];
}


function moderate_comment_api($comment_text){

    error_log("COMMENT MODERATION API CALLED");

    $url = "http://127.0.0.1:8000/cyberbullying/moderate_comment";

    $payload = json_encode([
        'text' => $comment_text
    ]);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);

    if($response === false){
        error_log("COMMENT API CURL ERROR: " . curl_error($ch));
        curl_close($ch);
        return ['ok' => false];
    }

    curl_close($ch);

    $decoded = json_decode($response, true);

    error_log("COMMENT AI RESULT: " . print_r($decoded, true));

    if(!$decoded){
        return ['ok' => false];
    }

    return [
        'ok' => true,
        'recommended_action' => $decoded['recommended_action'] ?? 'ALLOW',
        'reason' => $decoded['reason'] ?? '',
        'severity' => $decoded['severity'] ?? '',
        'evidence' => $decoded['evidence'] ?? []
    ];
}

