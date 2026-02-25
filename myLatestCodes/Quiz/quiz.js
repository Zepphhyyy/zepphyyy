// Quiz Application JavaScript

let currentQuestionIndex = 0;
let quizData = [];
let score = 0;
let answered = false;

// Initialize Quiz
document.addEventListener('DOMContentLoaded', function() {
    loadQuestions();
});

// Load Questions from Database via PHP
function loadQuestions() {
    fetch('get_questions.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                alert('Error loading quiz: ' + data.error);
                return;
            }
            quizData = data;
            if (quizData.length > 0) {
                displayQuestion();
            } else {
                alert('No questions available');
            }
        })
        .catch(error => console.error('Error:', error));
}

// Display Current Question
function displayQuestion() {
    if (currentQuestionIndex >= quizData.length) {
        endQuiz();
        return;
    }

    const question = quizData[currentQuestionIndex];
    answered = false;

    // Set question image
    const quizImage = document.getElementById('quiz-image');
    quizImage.src = question.image_url;
    quizImage.alt = question.question_text;

    // Clear previous buttons
    const optionsContainer = document.getElementById('options-container');
    optionsContainer.innerHTML = '';

    // Create answer buttons
    question.answers.forEach((answer, index) => {
        const button = document.createElement('button');
        button.className = 'option-button';
        button.textContent = answer.answer_text;
        button.dataset.answerId = answer.id;
        button.dataset.isCorrect = answer.is_correct;
        button.onclick = () => selectAnswer(button, answer.is_correct);
        optionsContainer.appendChild(button);
    });

    // Update progress
    document.getElementById('current-question').textContent = currentQuestionIndex + 1;
    document.getElementById('total-questions').textContent = quizData.length;
}

// Handle Answer Selection
function selectAnswer(button, isCorrect) {
    if (answered) return;

    answered = true;

    // Disable all buttons
    const buttons = document.querySelectorAll('.option-button');
    buttons.forEach(btn => btn.disabled = true);

    // Highlight correct/incorrect
    if (isCorrect) {
        button.classList.add('correct');
        score++;
    } else {
        button.classList.add('incorrect');
        // Show correct answer
        buttons.forEach(btn => {
            if (btn.dataset.isCorrect === 'true') {
                btn.classList.add('correct');
            }
        });
    }

    // Update score
    document.getElementById('score').textContent = score;

    // Show next button
    document.getElementById('next-button').style.display = 'block';
}

// Next Question
function nextQuestion() {
    currentQuestionIndex++;
    document.getElementById('next-button').style.display = 'none';
    displayQuestion();
}

// End Quiz
function endQuiz() {
    const quizContainer = document.getElementById('quiz-container');
    const percentage = (score / quizData.length * 100).toFixed(0);

    quizContainer.innerHTML = `
        <div class="results-container">
            <h2>Quiz Complete!</h2>
            <p>Your Score: <strong>${score}/${quizData.length}</strong></p>
            <p>Percentage: <strong>${percentage}%</strong></p>
            <button onclick="restartQuiz()" class="restart-button">Restart Quiz</button>
        </div>
    `;
}

// Restart Quiz
function restartQuiz() {
    currentQuestionIndex = 0;
    score = 0;
    answered = false;
    
    // Rebuild the quiz container structure
    const quizContainer = document.getElementById('quiz-container');
    quizContainer.innerHTML = `
        <div class="quiz-content">
            <div class="image-container">
                <img id="quiz-image" src="" alt="Question Image" class="quiz-image">
            </div>
            <div id="options-container" class="options-grid"></div>
            <button id="next-button" class="next-button" onclick="nextQuestion()" style="display: none;">Next Question</button>
        </div>
    `;
    
    // Update score display
    document.getElementById('score').textContent = score;
    
    // Display first question
    displayQuestion();
}
