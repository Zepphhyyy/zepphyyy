<?php
require_once 'config.php';

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = isset($_POST['question_text']) ? trim($_POST['question_text']) : '';
    $answer1 = isset($_POST['answer1']) ? trim($_POST['answer1']) : '';
    $answer2 = isset($_POST['answer2']) ? trim($_POST['answer2']) : '';
    $answer3 = isset($_POST['answer3']) ? trim($_POST['answer3']) : '';
    $answer4 = isset($_POST['answer4']) ? trim($_POST['answer4']) : '';
    $correct_answer = isset($_POST['correct_answer']) ? (int)$_POST['correct_answer'] : 0;

    // Validate inputs
    if (empty($question_text) || empty($answer1) || empty($answer2) || empty($answer3) || empty($answer4)) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } elseif ($correct_answer < 1 || $correct_answer > 4) {
        $message = 'Please select a correct answer.';
        $messageType = 'error';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Please upload an image.';
        $messageType = 'error';
    } else {
        // Read image file
        $image = file_get_contents($_FILES['image']['tmp_name']);
        $image_type = $_FILES['image']['type'];

        // Validate image type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($image_type, $allowed_types)) {
            $message = 'Invalid image type. Allowed types: JPEG, PNG, GIF, WebP.';
            $messageType = 'error';
        } else {
            // Insert question
            $stmt = $conn->prepare("INSERT INTO questions (question_text, image_data, image_type) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $question_text, $image, $image_type);
            
            // For binary data, we need to use send_long_data
            $stmt->send_long_data(1, $image);

            if ($stmt->execute()) {
                $question_id = $conn->insert_id;

                // Insert answers
                $answers = [$answer1, $answer2, $answer3, $answer4];
                $stmt_ans = $conn->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");

                $inserted_success = true;
                for ($i = 0; $i < 4; $i++) {
                    $is_correct = ($i + 1 === $correct_answer) ? 1 : 0;
                    $stmt_ans->bind_param("isi", $question_id, $answers[$i], $is_correct);
                    if (!$stmt_ans->execute()) {
                        $inserted_success = false;
                        break;
                    }
                }

                if ($inserted_success) {
                    $message = 'Question added successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error adding answers.';
                    $messageType = 'error';
                }
                $stmt_ans->close();
            } else {
                $message = 'Error adding question: ' . $conn->error;
                $messageType = 'error';
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Question - Quiz Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Century Gothic', Arial, sans-serif;
            background: linear-gradient(90deg, #e1e1e1 0%, #3a3a3a 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2em;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .message.show {
            display: block;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }

        input[type="text"],
        input[type="file"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="file"]:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }

        .answers-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .answer-option {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        input[type="radio"] {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add New Question</h1>

        <div class="message <?php echo $messageType; ?> <?php echo !empty($message) ? 'show' : ''; ?>">
            <?php echo $message; ?>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="question_text">Question Text:</label>
                <input type="text" id="question_text" name="question_text" placeholder="Enter the question" required>
            </div>

            <div class="form-group">
                <label for="image">Upload Image:</label>
                <input type="file" id="image" name="image" accept="image/*" required>
                <small>Max 5MB. Supported: JPEG, PNG, GIF, WebP</small>
            </div>

            <div class="form-group">
                <label>Answer Options:</label>
                <div class="answers-grid">
                    <div class="form-group">
                        <input type="text" name="answer1" placeholder="Option 1" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="answer2" placeholder="Option 2" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="answer3" placeholder="Option 3" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="answer4" placeholder="Option 4" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Select Correct Answer:</label>
                <select name="correct_answer" required>
                    <option value="">-- Select --</option>
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                    <option value="3">Option 3</option>
                    <option value="4">Option 4</option>
                </select>
            </div>

            <button type="submit">Add Question</button>
        </form>

        <div class="back-link">
            <a href="index.html">← Back to Quiz</a>
        </div>
    </div>
</body>
</html>
