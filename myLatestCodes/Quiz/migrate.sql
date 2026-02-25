-- SQL Migration: Add image_type column if it doesn't exist
-- Run this if you get the error: Unknown column 'q.image_type'

USE quiz_db;

ALTER TABLE questions ADD COLUMN image_type VARCHAR(50) DEFAULT 'image/jpeg' AFTER image_data;












INSERT INTO `answers` (`id`, `question_id`, `answer_text`, `is_correct`) VALUES (NULL, '3', 'Burj Khalifa', '0'), (NULL, '3', 'Statue of Liberty', '0'), (NULL, '3', 'Eiffel Tower', '1'), (NULL, '3', 'Big Ben', '0');