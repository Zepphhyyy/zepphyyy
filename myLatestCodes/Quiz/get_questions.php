<?php
header('Content-Type: application/json');

require_once 'config.php';

try {
    // Get all questions with their answers
    $query = "SELECT q.id, q.question_text, q.image_data, q.image_type,
                     a.id as answer_id, a.answer_text, a.is_correct
              FROM questions q
              LEFT JOIN answers a ON q.id = a.question_id
              ORDER BY q.id, a.id";

    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $questions = [];
    $currentQuestion = null;

    while ($row = $result->fetch_assoc()) {
        // If new question, add previous one to array
        if ($currentQuestion !== null && $currentQuestion['id'] != $row['id']) {
            $questions[] = $currentQuestion;
        }

        // Initialize new question if needed
        if ($currentQuestion === null || $currentQuestion['id'] != $row['id']) {
            // Convert binary image data to base64
            $imageData = base64_encode($row['image_data']);
            $imageType = $row['image_type'];
            $imageSrc = "data:{$imageType};base64,{$imageData}";

            $currentQuestion = [
                'id' => $row['id'],
                'question_text' => $row['question_text'],
                'image_url' => $imageSrc,
                'answers' => []
            ];
        }

        // Add answer to current question
        if ($row['answer_id'] !== null) {
            $currentQuestion['answers'][] = [
                'id' => $row['answer_id'],
                'answer_text' => $row['answer_text'],
                'is_correct' => (bool)$row['is_correct']
            ];
        }
    }

    // Add last question
    if ($currentQuestion !== null) {
        $questions[] = $currentQuestion;
    }

    // Shuffle questions
    shuffle($questions);
    
    // Shuffle answers for each question
    foreach ($questions as &$question) {
        shuffle($question['answers']);
    }

    echo json_encode($questions);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn->close();
}

?>
