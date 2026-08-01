-- Road2Job - Phase 13: Assessment System
-- Run after 012_phase12_interview.sql
-- Idempotent-safe: CREATE TABLE IF NOT EXISTS + seed guarded by a row-count check

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS assessment_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category ENUM('technical', 'coding', 'english', 'aptitude', 'communication') NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option ENUM('a', 'b', 'c', 'd') NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_assessment_questions_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    category ENUM('technical', 'coding', 'english', 'aptitude', 'communication') NOT NULL,
    score TINYINT UNSIGNED NULL,
    total_questions TINYINT UNSIGNED NOT NULL DEFAULT 5,
    percent TINYINT UNSIGNED NULL,
    passed TINYINT(1) NULL,
    started_at DATETIME NOT NULL,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_assessment_attempts_student_id (student_id),
    KEY idx_assessment_attempts_category (category),
    CONSTRAINT fk_assessment_attempts_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_attempt_answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assessment_attempt_id INT UNSIGNED NOT NULL,
    assessment_question_id INT UNSIGNED NOT NULL,
    order_index TINYINT UNSIGNED NOT NULL DEFAULT 0,
    selected_option ENUM('a', 'b', 'c', 'd') NULL,
    is_correct TINYINT(1) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_aaa_attempt_id (assessment_attempt_id),
    KEY idx_aaa_question_id (assessment_question_id),
    CONSTRAINT fk_aaa_attempt FOREIGN KEY (assessment_attempt_id) REFERENCES assessment_attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_aaa_question FOREIGN KEY (assessment_question_id) REFERENCES assessment_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed a starter question bank (10 per category), only if empty (safe to re-run)
SET @question_count = (SELECT COUNT(*) FROM assessment_questions);

INSERT INTO assessment_questions (category, question_text, option_a, option_b, option_c, option_d, correct_option, created_at, updated_at)
SELECT * FROM (
    SELECT 'technical' AS category, 'Which HTTP method is typically used to update an existing resource?' AS question_text, 'GET' AS option_a, 'POST' AS option_b, 'PUT' AS option_c, 'DELETE' AS option_d, 'c' AS correct_option, NOW() AS created_at, NOW() AS updated_at
    UNION ALL SELECT 'technical', 'Which of these is a NoSQL database?', 'MySQL', 'PostgreSQL', 'MongoDB', 'SQLite', 'c', NOW(), NOW()
    UNION ALL SELECT 'technical', 'What does CSS stand for?', 'Computer Style Sheets', 'Cascading Style Sheets', 'Creative Style System', 'Colorful Style Sheets', 'b', NOW(), NOW()
    UNION ALL SELECT 'technical', 'Which data structure uses LIFO (Last In First Out) order?', 'Queue', 'Stack', 'Array', 'Linked List', 'b', NOW(), NOW()
    UNION ALL SELECT 'technical', 'What is the primary purpose of an index in a database table?', 'Encrypt data', 'Speed up data retrieval', 'Reduce storage size', 'Enforce foreign keys', 'b', NOW(), NOW()
    UNION ALL SELECT 'technical', 'Which of these is used for version control?', 'Docker', 'Git', 'Jenkins', 'Kubernetes', 'b', NOW(), NOW()
    UNION ALL SELECT 'technical', 'In REST APIs, which status code means \"Not Found\"?', '200', '301', '404', '500', 'c', NOW(), NOW()
    UNION ALL SELECT 'technical', 'What does API stand for?', 'Application Programming Interface', 'Advanced Program Integration', 'Application Process Index', 'Applied Programming Instruction', 'a', NOW(), NOW()
    UNION ALL SELECT 'technical', 'Which language runs natively in a web browser?', 'Python', 'Java', 'JavaScript', 'C++', 'c', NOW(), NOW()
    UNION ALL SELECT 'technical', 'What is the main purpose of CSS media queries?', 'Database queries', 'Responsive design', 'Form validation', 'API requests', 'b', NOW(), NOW()

    UNION ALL SELECT 'coding', 'What will `console.log(2 + \"2\")` output in JavaScript?', '4', '"22"', 'NaN', 'undefined', 'b', NOW(), NOW()
    UNION ALL SELECT 'coding', 'What is the output of `print(5 // 2)` in Python?', '2.5', '2', '3', 'Error', 'b', NOW(), NOW()
    UNION ALL SELECT 'coding', 'In PHP, what does `===` check that `==` does not?', 'Nothing, they are identical', 'Value AND type equality', 'Only type', 'Only value', 'b', NOW(), NOW()
    UNION ALL SELECT 'coding', 'What is the time complexity of searching in a balanced binary search tree?', 'O(1)', 'O(n)', 'O(log n)', 'O(n^2)', 'c', NOW(), NOW()
    UNION ALL SELECT 'coding', 'Which line contains the bug? Line 1: function add(a, b) { Line 2:   return a + b Line 3: } Line 4: console.log(add(2, 3);', 'Line 1', 'Line 2', 'Line 3', 'Line 4', 'd', NOW(), NOW()
    UNION ALL SELECT 'coding', 'What does this SQL return: SELECT COUNT(*) FROM users WHERE 1=0;', 'All rows', 'NULL', '0', 'An error', 'c', NOW(), NOW()
    UNION ALL SELECT 'coding', 'What is the output of `[1,2,3].length` in JavaScript?', '2', '3', '4', 'undefined', 'b', NOW(), NOW()
    UNION ALL SELECT 'coding', 'In a for loop `for(i=0; i<5; i++)`, how many times does it run?', '4', '5', '6', 'Infinite', 'b', NOW(), NOW()
    UNION ALL SELECT 'coding', 'What does `array_merge()` do in PHP?', 'Sorts an array', 'Combines two or more arrays', 'Removes duplicates', 'Reverses an array', 'b', NOW(), NOW()
    UNION ALL SELECT 'coding', 'Which of these correctly declares a constant in JavaScript?', 'var x = 5', 'let x = 5', 'const x = 5', 'static x = 5', 'c', NOW(), NOW()

    UNION ALL SELECT 'english', 'Choose the correctly spelled word.', 'Recieve', 'Receive', 'Receeve', 'Receve', 'b', NOW(), NOW()
    UNION ALL SELECT 'english', 'Fill in the blank: She ___ to the market yesterday.', 'go', 'goes', 'went', 'going', 'c', NOW(), NOW()
    UNION ALL SELECT 'english', 'Choose the correct synonym for "Happy".', 'Sad', 'Joyful', 'Angry', 'Tired', 'b', NOW(), NOW()
    UNION ALL SELECT 'english', 'Choose the correct antonym for "Increase".', 'Grow', 'Expand', 'Decrease', 'Raise', 'c', NOW(), NOW()
    UNION ALL SELECT 'english', 'Which sentence is grammatically correct?', 'He don''t like it.', 'He doesn''t likes it.', 'He doesn''t like it.', 'He not like it.', 'c', NOW(), NOW()
    UNION ALL SELECT 'english', 'Identify the noun in: "The quick fox jumps."', 'Quick', 'Fox', 'Jumps', 'The', 'b', NOW(), NOW()
    UNION ALL SELECT 'english', 'What is the plural of "child"?', 'Childs', 'Childes', 'Children', 'Childrens', 'c', NOW(), NOW()
    UNION ALL SELECT 'english', 'Choose the correct form: "I have ___ that movie already."', 'see', 'saw', 'seen', 'seeing', 'c', NOW(), NOW()
    UNION ALL SELECT 'english', 'Which word is an adverb in: "She sings beautifully."', 'She', 'Sings', 'Beautifully', 'None', 'c', NOW(), NOW()
    UNION ALL SELECT 'english', 'Choose the correct preposition: "The book is ___ the table."', 'in', 'on', 'at', 'by', 'b', NOW(), NOW()

    UNION ALL SELECT 'aptitude', 'If a train travels 60 km in 1 hour, how far does it travel in 2.5 hours at the same speed?', '120 km', '150 km', '100 km', '180 km', 'b', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'What is 15% of 200?', '20', '25', '30', '35', 'c', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'Find the next number in the series: 2, 4, 8, 16, ?', '20', '24', '32', '18', 'c', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'If 5 workers can build a wall in 10 days, how many days will 10 workers take?', '20 days', '5 days', '10 days', '2 days', 'b', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'A shirt costs Rs.500 after a 20% discount. What was the original price?', 'Rs.600', 'Rs.625', 'Rs.650', 'Rs.700', 'b', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'What is the average of 10, 20, 30, and 40?', '20', '25', '30', '35', 'b', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'If today is Monday, what day will it be after 10 days?', 'Wednesday', 'Thursday', 'Friday', 'Tuesday', 'a', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'A is twice as old as B. If B is 12, how old is A?', '6', '12', '24', '36', 'c', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'What comes next: A, C, E, G, ?', 'H', 'I', 'J', 'K', 'b', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'A car covers 240 km using 20 litres of fuel. What is its mileage?', '10 km/l', '12 km/l', '14 km/l', '16 km/l', 'b', NOW(), NOW()

    UNION ALL SELECT 'communication', 'In a professional email, which greeting is most appropriate?', 'Hey!', 'Yo,', 'Dear Sir/Madam,', 'Sup,', 'c', NOW(), NOW()
    UNION ALL SELECT 'communication', 'What is "active listening" primarily about?', 'Speaking loudly', 'Fully focusing on and understanding the speaker', 'Interrupting to add ideas', 'Multitasking while listening', 'b', NOW(), NOW()
    UNION ALL SELECT 'communication', 'Which is the best way to give constructive feedback?', 'Focus only on flaws', 'Be vague and general', 'Be specific, balanced, and respectful', 'Give feedback publicly to embarrass', 'c', NOW(), NOW()
    UNION ALL SELECT 'communication', 'In a job interview, what does maintaining eye contact typically signal?', 'Aggression', 'Confidence and engagement', 'Nervousness', 'Disinterest', 'b', NOW(), NOW()
    UNION ALL SELECT 'communication', 'What is the purpose of a follow-up email after an interview?', 'To negotiate salary', 'To express thanks and reinforce interest', 'To ask for feedback on other candidates', 'To reschedule the interview', 'b', NOW(), NOW()
    UNION ALL SELECT 'communication', 'Which body language typically suggests openness in a conversation?', 'Crossed arms', 'Avoiding eye contact', 'Relaxed posture and nodding', 'Looking at your phone', 'c', NOW(), NOW()
    UNION ALL SELECT 'communication', 'What is the best approach when you disagree with a colleague in a meeting?', 'Stay silent and complain later', 'Interrupt and argue loudly', 'Respectfully share your perspective with reasons', 'Ignore the discussion entirely', 'c', NOW(), NOW()
    UNION ALL SELECT 'communication', 'Why is clarity important in written communication?', 'It makes emails longer', 'It reduces misunderstanding', 'It impresses the reader with vocabulary', 'It is not important', 'b', NOW(), NOW()
    UNION ALL SELECT 'communication', 'What does "elevator pitch" refer to?', 'A sales pitch for elevators', 'A short, persuasive summary of yourself or an idea', 'A complaint about slow elevators', 'A long detailed presentation', 'b', NOW(), NOW()
    UNION ALL SELECT 'communication', 'When giving a presentation, what best helps keep the audience engaged?', 'Reading directly from slides', 'Speaking in a monotone voice', 'Clear structure, eye contact, and pacing', 'Using very technical jargon throughout', 'c', NOW(), NOW()
) AS seed_rows
WHERE @question_count = 0;
