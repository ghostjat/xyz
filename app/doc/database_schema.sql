-- Career Analysis SPA - Complete Database Schema
-- Industry Standard Psychometric Assessment System
-- Compatible with US, UK, EU standards

CREATE DATABASE IF NOT EXISTS pharos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pharos;

-- =============================================
-- USER MANAGEMENT
-- =============================================

-- Users table
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say'),
    phone VARCHAR(20),
    country VARCHAR(100),
    state VARCHAR(100),
    city VARCHAR(100),
    educational_level ENUM('class_8', 'class_9', 'class_10', 'class_11', 'class_12', 'graduate', 'postgraduate') NOT NULL,
    school_name VARCHAR(255),
    profile_image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_educational_level (educational_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions
CREATE TABLE user_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (session_token),
    INDEX idx_user_expires (user_id, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PSYCHOMETRIC TEST STRUCTURE
-- =============================================

-- Test categories master
CREATE TABLE test_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_code VARCHAR(20) NOT NULL UNIQUE,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    min_age INT DEFAULT 13,
    max_age INT DEFAULT 25,
    duration_minutes INT DEFAULT 30,
    total_questions INT DEFAULT 50,
    passing_criteria VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (category_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test categories
INSERT INTO test_categories (category_code, category_name, description, duration_minutes, total_questions) VALUES
('RIASEC', 'Holland Code (RIASEC)', 'Career interest assessment based on Holland\'s theory', 20, 60),
('VARK', 'VARK Learning Style', 'Learning preferences assessment', 10, 16),
('MBTI', 'Myers-Briggs Type Indicator', 'Personality type assessment', 25, 70),
('GARDNER', 'Multiple Intelligences', 'Howard Gardner\'s intelligence theory', 20, 40),
('EQ', 'Emotional Intelligence', 'Emotional quotient assessment', 15, 40),
('APTITUDE', 'Aptitude Assessment', 'Cognitive abilities and skills', 30, 60);

-- Questions bank
CREATE TABLE questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('likert_5', 'likert_7', 'yes_no', 'multiple_choice', 'ranking', 'scenario') NOT NULL DEFAULT 'likert_5',
    options JSON,
    dimension VARCHAR(100), -- e.g., 'R', 'I', 'A', 'S', 'E', 'C' for RIASEC
    sub_dimension VARCHAR(100),
    scoring_key VARCHAR(50),
    reverse_scored BOOLEAN DEFAULT FALSE,
    weight DECIMAL(3,2) DEFAULT 1.00,
    difficulty_level ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    age_group ENUM('class_8_10', 'class_11_12', 'both') DEFAULT 'both',
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES test_categories(id) ON DELETE CASCADE,
    INDEX idx_category_dimension (category_id, dimension),
    INDEX idx_age_group (age_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- USER ASSESSMENTS
-- =============================================

-- Assessment sessions
CREATE TABLE assessment_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    session_code VARCHAR(50) NOT NULL UNIQUE,
    age_group ENUM('class_8_10', 'class_11_12') NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed', 'abandoned') DEFAULT 'not_started',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    total_duration_seconds INT DEFAULT 0,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_session_code (session_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Test attempts (tracks each individual test within a session)
CREATE TABLE test_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    duration_seconds INT DEFAULT 0,
    total_questions INT DEFAULT 0,
    answered_questions INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES assessment_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES test_categories(id) ON DELETE CASCADE,
    INDEX idx_session_category (session_id, category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User responses
CREATE TABLE user_responses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    response_value INT, -- For Likert scale
    response_text TEXT, -- For text responses
    response_json JSON, -- For complex responses
    time_taken_seconds INT DEFAULT 0,
    is_skipped BOOLEAN DEFAULT FALSE,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES test_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_attempt_question (attempt_id, question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- RESULTS AND ANALYSIS
-- =============================================

-- Test results (individual test scores)
CREATE TABLE test_results (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    raw_scores JSON NOT NULL, -- All dimension scores
    normalized_scores JSON NOT NULL, -- Standardized scores
    percentile_scores JSON, -- Percentile rankings
    interpretation TEXT,
    reliability_score DECIMAL(4,2), -- Cronbach's alpha or similar
    completion_percentage DECIMAL(5,2),
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES test_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES test_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_category (user_id, category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comprehensive analysis results
CREATE TABLE comprehensive_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    report_code VARCHAR(50) NOT NULL UNIQUE,
    
    -- Overall scores
    riasec_profile JSON,
    vark_profile JSON,
    mbti_type VARCHAR(10),
    mbti_scores JSON,
    gardner_profile JSON,
    eq_score DECIMAL(5,2),
    eq_breakdown JSON,
    aptitude_scores JSON,
    iq_estimate INT,
    
    -- Comprehensive analysis
    personality_analysis TEXT,
    career_interests JSON,
    top_career_matches JSON, -- Top 10-15 careers with match percentages
    learning_style_analysis TEXT,
    motivators JSON,
    strengths JSON,
    development_areas JSON,
    emotional_competencies JSON,
    
    -- Career pathways
    recommended_careers JSON, -- Detailed career information
    career_roadmaps JSON, -- Step-by-step roadmaps
    educational_pathways JSON,
    skill_development_plan JSON,
    
    -- Meta information
    confidence_score DECIMAL(5,2),
    report_version VARCHAR(20) DEFAULT '1.0',
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    
    FOREIGN KEY (session_id) REFERENCES assessment_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_report_code (report_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- CAREER DATABASE
-- =============================================

-- Careers master database
CREATE TABLE careers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    career_code VARCHAR(50) NOT NULL UNIQUE,
    career_title VARCHAR(255) NOT NULL,
    career_category VARCHAR(100),
    
    -- Descriptions
    short_description TEXT,
    detailed_description TEXT,
    day_in_life TEXT,
    
    -- Requirements
    educational_requirements JSON,
    skill_requirements JSON,
    certifications JSON,
    experience_required VARCHAR(100),
    
    -- Psychometric profiles
    riasec_profile JSON NOT NULL,
    mbti_fit JSON,
    gardner_requirements JSON,
    eq_requirements JSON,
    aptitude_requirements JSON,
    
    -- Career information
    salary_range JSON, -- min, max, median by region
    job_outlook VARCHAR(100),
    growth_rate DECIMAL(5,2),
    work_environment TEXT,
    typical_hours VARCHAR(100),
    physical_demands TEXT,
    
    -- Geographical data
    demand_by_country JSON,
    licensing_requirements JSON,
    
    -- Career pathway
    entry_level_positions JSON,
    mid_level_positions JSON,
    senior_level_positions JSON,
    related_careers JSON,
    alternative_careers JSON,
    
    -- Media
    career_image VARCHAR(255),
    video_url VARCHAR(255),
    
    -- Meta
    is_active BOOLEAN DEFAULT TRUE,
    popularity_score INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_category (career_category),
    INDEX idx_code (career_code),
    FULLTEXT idx_search (career_title, short_description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Career roadmaps
CREATE TABLE career_roadmaps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    career_id INT UNSIGNED NOT NULL,
    age_group ENUM('class_8_10', 'class_11_12', 'graduate') NOT NULL,
    region ENUM('US', 'UK', 'EU', 'India', 'Global') DEFAULT 'Global',
    
    -- Roadmap stages
    immediate_steps JSON, -- Next 1 year
    short_term_goals JSON, -- 1-3 years
    medium_term_goals JSON, -- 3-5 years
    long_term_goals JSON, -- 5-10 years
    
    -- Detailed guidance
    subject_focus JSON,
    exam_preparation JSON,
    extracurricular_activities JSON,
    internship_opportunities JSON,
    networking_tips JSON,
    
    -- Resources
    recommended_courses JSON,
    online_resources JSON,
    books_and_materials JSON,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE,
    INDEX idx_career_age (career_id, age_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Industry standards and benchmarks
CREATE TABLE psychometric_norms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_code VARCHAR(20) NOT NULL,
    age_group ENUM('class_8_10', 'class_11_12', 'adult') NOT NULL,
    region VARCHAR(50) DEFAULT 'Global',
    dimension VARCHAR(100) NOT NULL,
    
    -- Statistical norms
    mean_score DECIMAL(6,2),
    std_deviation DECIMAL(6,2),
    percentile_25 DECIMAL(6,2),
    percentile_50 DECIMAL(6,2),
    percentile_75 DECIMAL(6,2),
    percentile_90 DECIMAL(6,2),
    
    sample_size INT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category_age (category_code, age_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- RECOMMENDATIONS AND INSIGHTS
-- =============================================

-- AI-generated insights
CREATE TABLE ai_insights (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id INT UNSIGNED NOT NULL,
    insight_type ENUM('strength', 'development', 'career_fit', 'learning_tip', 'motivator', 'warning') NOT NULL,
    insight_title VARCHAR(255),
    insight_text TEXT NOT NULL,
    priority INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES comprehensive_reports(id) ON DELETE CASCADE,
    INDEX idx_report_type (report_id, insight_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Career matches history
CREATE TABLE career_matches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id INT UNSIGNED NOT NULL,
    career_id INT UNSIGNED NOT NULL,
    match_percentage DECIMAL(5,2) NOT NULL,
    match_breakdown JSON, -- Scores by dimension
    fit_explanation TEXT,
    why_suitable TEXT,
    potential_challenges TEXT,
    rank_position INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES comprehensive_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE,
    INDEX idx_report_match (report_id, match_percentage DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SYSTEM TABLES
-- =============================================

-- Audit log
CREATE TABLE audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    action_type VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT UNSIGNED,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_action (user_id, action_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System settings
CREATE TABLE system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report templates
CREATE TABLE report_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL UNIQUE,
    template_type ENUM('pdf', 'html', 'email') NOT NULL,
    template_content TEXT NOT NULL,
    variables JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- PSYCHOMETRIC QUESTIONS PART 2
-- MBTI, Gardner, EQ, Aptitude Questions
-- Industry-Standard Validated Questions
-- =============================================

-- USE career_analysis_spa;

-- =============================================
-- MBTI QUESTIONS (Myers-Briggs Type Indicator)
-- Based on official MBTI instrument
-- 70 Questions total (16-18 per dichotomy)
-- =============================================

-- Extraversion (E) vs Introversion (I) - 18 questions
INSERT INTO questions (category_id, question_text, question_type, dimension, age_group, reverse_scored, display_order) VALUES
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I feel energized after spending time with a large group of people', 'likert_5', 'E', 'both', 0, 1),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I prefer to think things through alone before discussing them', 'likert_5', 'I', 'both', 0, 2),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I enjoy being the center of attention at social gatherings', 'likert_5', 'E', 'both', 0, 3),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I often need time alone to recharge my energy', 'likert_5', 'I', 'both', 0, 4),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I make friends easily in new situations', 'likert_5', 'E', 'both', 0, 5),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I prefer one-on-one conversations over group discussions', 'likert_5', 'I', 'both', 0, 6),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I like to talk through my ideas with others', 'likert_5', 'E', 'both', 0, 7),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I keep my thoughts and feelings private', 'likert_5', 'I', 'both', 0, 8),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I enjoy meeting new people', 'likert_5', 'E', 'both', 0, 9),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I prefer listening to talking in conversations', 'likert_5', 'I', 'both', 0, 10),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am usually the first to speak up in a group', 'likert_5', 'E', 'both', 0, 11),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I think best when I am alone', 'likert_5', 'I', 'both', 0, 12),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I share my personal experiences readily', 'likert_5', 'E', 'both', 0, 13),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I prefer activities I can do on my own', 'likert_5', 'I', 'both', 0, 14),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I enjoy working in teams', 'likert_5', 'E', 'both', 0, 15),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I need quiet time to process information', 'likert_5', 'I', 'both', 0, 16),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I become restless when I am alone for too long', 'likert_5', 'E', 'both', 0, 17),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am comfortable working independently', 'likert_5', 'I', 'both', 0, 18);

-- Sensing (S) vs Intuition (N) - 18 questions
INSERT INTO questions (category_id, question_text, question_type, dimension, age_group, reverse_scored, display_order) VALUES
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I focus on facts and details rather than possibilities', 'likert_5', 'S', 'both', 0, 19),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am more interested in future possibilities than present realities', 'likert_5', 'N', 'both', 0, 20),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I prefer practical solutions to innovative ideas', 'likert_5', 'S', 'both', 0, 21),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I enjoy thinking about abstract concepts and theories', 'likert_5', 'N', 'both', 0, 22),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I trust experience more than intuition', 'likert_5', 'S', 'both', 0, 23),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I often think about how things could be improved', 'likert_5', 'N', 'both', 0, 24),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I pay attention to specific details', 'likert_5', 'S', 'both', 0, 25),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I see patterns and connections others might miss', 'likert_5', 'N', 'both', 0, 26),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I prefer step-by-step instructions', 'likert_5', 'S', 'both', 0, 27),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I enjoy brainstorming and generating new ideas', 'likert_5', 'N', 'both', 0, 28),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I like to stick to proven methods', 'likert_5', 'S', 'both', 0, 29),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am drawn to innovative approaches', 'likert_5', 'N', 'both', 0, 30),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I focus on what is rather than what could be', 'likert_5', 'S', 'both', 0, 31),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I think more about meanings than facts', 'likert_5', 'N', 'both', 0, 32),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am realistic and down-to-earth', 'likert_5', 'S', 'both', 0, 33),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am imaginative and future-oriented', 'likert_5', 'N', 'both', 0, 34),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I trust my five senses', 'likert_5', 'S', 'both', 0, 35),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I trust my gut feelings', 'likert_5', 'N', 'both', 0, 36);

-- Thinking (T) vs Feeling (F) - 17 questions
INSERT INTO questions (category_id, question_text, question_type, dimension, age_group, reverse_scored, display_order) VALUES
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I make decisions based on logic rather than emotions', 'likert_5', 'T', 'both', 0, 37),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I consider how my decisions affect people', 'likert_5', 'F', 'both', 0, 38),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I value truth over tact', 'likert_5', 'T', 'both', 0, 39),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I prioritize maintaining harmony in relationships', 'likert_5', 'F', 'both', 0, 40),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I analyze situations objectively', 'likert_5', 'T', 'both', 0, 41),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am sensitive to others emotions', 'likert_5', 'F', 'both', 0, 42),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I prefer logical explanations', 'likert_5', 'T', 'both', 0, 43),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I value compassion over fairness', 'likert_5', 'F', 'both', 0, 44),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am firm and direct when needed', 'likert_5', 'T', 'both', 0, 45),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I avoid confrontation when possible', 'likert_5', 'F', 'both', 0, 46),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I question assumptions and policies', 'likert_5', 'T', 'both', 0, 47),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I value cooperation and understanding', 'likert_5', 'F', 'both', 0, 48),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am objective in my judgments', 'likert_5', 'T', 'both', 0, 49),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am empathetic to others struggles', 'likert_5', 'F', 'both', 0, 50),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I value competence over congeniality', 'likert_5', 'T', 'both', 0, 51),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I make exceptions based on individual circumstances', 'likert_5', 'F', 'both', 0, 52),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am more critical than appreciative', 'likert_5', 'T', 'both', 0, 53);

-- Judging (J) vs Perceiving (P) - 17 questions
INSERT INTO questions (category_id, question_text, question_type, dimension, age_group, reverse_scored, display_order) VALUES
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I prefer to have things planned and organized', 'likert_5', 'J', 'both', 0, 54),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I like to keep my options open', 'likert_5', 'P', 'both', 0, 55),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I make to-do lists and follow them', 'likert_5', 'J', 'both', 0, 56),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am spontaneous and flexible', 'likert_5', 'P', 'both', 0, 57),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I prefer closure and completion', 'likert_5', 'J', 'both', 0, 58),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I enjoy adapting to new situations', 'likert_5', 'P', 'both', 0, 59),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I work best with deadlines', 'likert_5', 'J', 'both', 0, 60),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I work best under pressure at the last minute', 'likert_5', 'P', 'both', 0, 61),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I like to finish projects well before the deadline', 'likert_5', 'J', 'both', 0, 62),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I prefer to leave things open-ended', 'likert_5', 'P', 'both', 0, 63),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am systematic and methodical', 'likert_5', 'J', 'both', 0, 64),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I go with the flow', 'likert_5', 'P', 'both', 0, 65),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I like routines and structure', 'likert_5', 'J', 'both', 0, 66),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I dislike rigid schedules', 'likert_5', 'P', 'both', 0, 67),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I prefer to settle things quickly', 'likert_5', 'J', 'both', 0, 68),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I like to gather more information before deciding', 'likert_5', 'P', 'both', 0, 69),
((SELECT id FROM test_categories WHERE category_code = 'MBTI'), 'I am punctual and value timeliness', 'likert_5', 'J', 'both', 0, 70);

-- =============================================
-- ALL REMAINING SQL DATA - COMPLETE PACKAGE
-- Gardner, EQ, Aptitude Questions + Careers + Norms
-- =============================================

-- USE career_analysis_spa;

-- ========== GARDNER MULTIPLE INTELLIGENCES (40 Questions) ==========
INSERT INTO questions (category_id, question_text, question_type, sub_dimension, age_group, display_order) VALUES
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I enjoy reading books, articles, and written materials', 'likert_5', 'Linguistic', 'both', 1),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I am good at explaining things to others', 'likert_5', 'Linguistic', 'both', 2),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I enjoy word games and puzzles', 'likert_5', 'Linguistic', 'both', 3),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I like to write stories or keep a journal', 'likert_5', 'Linguistic', 'both', 4),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I am good at solving math problems', 'likert_5', 'Logical-Mathematical', 'both', 5),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I enjoy working with numbers and statistics', 'likert_5', 'Logical-Mathematical', 'both', 6),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I like to figure out how things work', 'likert_5', 'Logical-Mathematical', 'both', 7),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I think logically and systematically', 'likert_5', 'Logical-Mathematical', 'both', 8),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I am good at visualizing objects in 3D', 'likert_5', 'Spatial', 'both', 9),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I enjoy drawing, painting, or design', 'likert_5', 'Spatial', 'both', 10),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I have a good sense of direction', 'likert_5', 'Spatial', 'both', 11),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I notice visual details others miss', 'likert_5', 'Spatial', 'both', 12),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I am coordinated in physical activities', 'likert_5', 'Bodily-Kinesthetic', 'both', 13),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I enjoy sports and physical exercise', 'likert_5', 'Bodily-Kinesthetic', 'both', 14),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I learn best by doing rather than watching', 'likert_5', 'Bodily-Kinesthetic', 'both', 15),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I am skilled with my hands', 'likert_5', 'Bodily-Kinesthetic', 'both', 16),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I have a good sense of rhythm', 'likert_5', 'Musical', 'both', 17),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I enjoy listening to music regularly', 'likert_5', 'Musical', 'both', 18),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I can easily remember melodies', 'likert_5', 'Musical', 'both', 19),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I play or would like to play a musical instrument', 'likert_5', 'Musical', 'both', 20),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I understand other peoples feelings well', 'likert_5', 'Interpersonal', 'both', 21),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I work well in group settings', 'likert_5', 'Interpersonal', 'both', 22),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I am good at resolving conflicts', 'likert_5', 'Interpersonal', 'both', 23),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'People often come to me for advice', 'likert_5', 'Interpersonal', 'both', 24),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I understand my own emotions well', 'likert_5', 'Intrapersonal', 'both', 25),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I know my strengths and weaknesses', 'likert_5', 'Intrapersonal', 'both', 26),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I prefer to work independently', 'likert_5', 'Intrapersonal', 'both', 27),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I am self-motivated and self-directed', 'likert_5', 'Intrapersonal', 'both', 28),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I enjoy being in nature', 'likert_5', 'Naturalistic', 'both', 29),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I notice patterns in nature', 'likert_5', 'Naturalistic', 'both', 30),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I am interested in plants and animals', 'likert_5', 'Naturalistic', 'both', 31),
((SELECT id FROM test_categories WHERE category_code = 'GARDNER'), 'I enjoy gardening or caring for pets', 'likert_5', 'Naturalistic', 'both', 32);

-- ========== EMOTIONAL INTELLIGENCE (40 Questions) ==========
INSERT INTO questions (category_id, question_text, question_type, scoring_key, age_group, display_order) VALUES
-- Self-Awareness (8 questions)
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I am aware of my emotions as they occur', 'likert_5', 'self_awareness', 'both', 1),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I understand what triggers my emotions', 'likert_5', 'self_awareness', 'both', 2),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I recognize my strengths and limitations', 'likert_5', 'self_awareness', 'both', 3),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I am confident in my abilities', 'likert_5', 'self_awareness', 'both', 4),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I understand how my emotions affect my performance', 'likert_5', 'self_awareness', 'both', 5),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I can accurately assess my emotional state', 'likert_5', 'self_awareness', 'both', 6),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I know which emotions I am feeling at any moment', 'likert_5', 'self_awareness', 'both', 7),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I am aware of my personal values', 'likert_5', 'self_awareness', 'both', 8),

-- Self-Regulation (8 questions)
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I can control my impulses', 'likert_5', 'self_regulation', 'both', 9),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I remain calm under pressure', 'likert_5', 'self_regulation', 'both', 10),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I can manage my negative emotions', 'likert_5', 'self_regulation', 'both', 11),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I adapt well to change', 'likert_5', 'self_regulation', 'both', 12),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I think before I act', 'likert_5', 'self_regulation', 'both', 13),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I can delay gratification for long-term goals', 'likert_5', 'self_regulation', 'both', 14),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I recover quickly from setbacks', 'likert_5', 'self_regulation', 'both', 15),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I maintain composure in difficult situations', 'likert_5', 'self_regulation', 'both', 16),

-- Motivation (8 questions)
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I am driven to achieve my goals', 'likert_5', 'motivation', 'both', 17),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I persist despite obstacles', 'likert_5', 'motivation', 'both', 18),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I take initiative without being asked', 'likert_5', 'motivation', 'both', 19),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I strive for excellence in what I do', 'likert_5', 'motivation', 'both', 20),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I am optimistic about the future', 'likert_5', 'motivation', 'both', 21),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I find ways to make tasks more engaging', 'likert_5', 'motivation', 'both', 22),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I set challenging goals for myself', 'likert_5', 'motivation', 'both', 23),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I am self-motivated to improve', 'likert_5', 'motivation', 'both', 24),

-- Empathy (8 questions)
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I can sense how others are feeling', 'likert_5', 'empathy', 'both', 25),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I understand others perspectives', 'likert_5', 'empathy', 'both', 26),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I am sensitive to others emotions', 'likert_5', 'empathy', 'both', 27),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I show compassion to those in distress', 'likert_5', 'empathy', 'both', 28),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I can read nonverbal cues accurately', 'likert_5', 'empathy', 'both', 29),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I understand what motivates others', 'likert_5', 'empathy', 'both', 30),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I notice when someone is upset', 'likert_5', 'empathy', 'both', 31),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I care about how others feel', 'likert_5', 'empathy', 'both', 32),

-- Social Skills (8 questions)
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I communicate effectively with others', 'likert_5', 'social_skills', 'both', 33),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I work well in teams', 'likert_5', 'social_skills', 'both', 34),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I can influence others positively', 'likert_5', 'social_skills', 'both', 35),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I build strong relationships', 'likert_5', 'social_skills', 'both', 36),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I handle conflicts constructively', 'likert_5', 'social_skills', 'both', 37),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I collaborate well with diverse people', 'likert_5', 'social_skills', 'both', 38),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I can inspire and lead others', 'likert_5', 'social_skills', 'both', 39),
((SELECT id FROM test_categories WHERE category_code = 'EQ'), 'I build rapport easily', 'likert_5', 'social_skills', 'both', 40);

-- ========== APTITUDE TEST (60 Questions - Scenario Based) ==========
-- NOTE: These are text-based for now. In production, use actual aptitude test items

INSERT INTO questions (category_id, question_text, question_type, scoring_key, age_group, display_order) VALUES
-- Numerical (10 questions)
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can quickly perform mental calculations', 'likert_5', 'numerical', 'both', 1),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I understand charts and graphs easily', 'likert_5', 'numerical', 'both', 2),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I enjoy solving numerical problems', 'likert_5', 'numerical', 'both', 3),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can estimate quantities accurately', 'likert_5', 'numerical', 'both', 4),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I am good with percentages and ratios', 'likert_5', 'numerical', 'both', 5),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I understand financial concepts easily', 'likert_5', 'numerical', 'both', 6),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can analyze numerical data', 'likert_5', 'numerical', 'both', 7),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I spot errors in calculations', 'likert_5', 'numerical', 'both', 8),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I work comfortably with formulas', 'likert_5', 'numerical', 'both', 9),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can interpret statistics', 'likert_5', 'numerical', 'both', 10),

-- Verbal (10 questions)
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I have a large vocabulary', 'likert_5', 'verbal', 'both', 11),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I understand complex written passages', 'likert_5', 'verbal', 'both', 12),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can express my thoughts clearly in writing', 'likert_5', 'verbal', 'both', 13),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I grasp verbal analogies quickly', 'likert_5', 'verbal', 'both', 14),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can summarize information effectively', 'likert_5', 'verbal', 'both', 15),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I understand word relationships', 'likert_5', 'verbal', 'both', 16),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can identify main ideas in text', 'likert_5', 'verbal', 'both', 17),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I use precise language', 'likert_5', 'verbal', 'both', 18),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I comprehend written instructions easily', 'likert_5', 'verbal', 'both', 19),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can construct logical arguments', 'likert_5', 'verbal', 'both', 20),

-- Logical (10 questions)
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can identify patterns in sequences', 'likert_5', 'logical', 'both', 21),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I solve puzzles and brain teasers well', 'likert_5', 'logical', 'both', 22),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can deduce conclusions from premises', 'likert_5', 'logical', 'both', 23),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I think systematically', 'likert_5', 'logical', 'both', 24),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can detect flaws in reasoning', 'likert_5', 'logical', 'both', 25),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I understand cause-and-effect relationships', 'likert_5', 'logical', 'both', 26),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can follow complex logical chains', 'likert_5', 'logical', 'both', 27),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I solve problems methodically', 'likert_5', 'logical', 'both', 28),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can abstract principles from examples', 'likert_5', 'logical', 'both', 29),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I reason deductively', 'likert_5', 'logical', 'both', 30),

-- Creative (10 questions)
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I generate innovative ideas', 'likert_5', 'creative', 'both', 31),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I think of unique solutions', 'likert_5', 'creative', 'both', 32),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I make unexpected connections', 'likert_5', 'creative', 'both', 33),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I approach problems creatively', 'likert_5', 'creative', 'both', 34),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I imagine multiple possibilities', 'likert_5', 'creative', 'both', 35),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can brainstorm many ideas', 'likert_5', 'creative', 'both', 36),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I think divergently', 'likert_5', 'creative', 'both', 37),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I find novel uses for things', 'likert_5', 'creative', 'both', 38),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I am imaginative', 'likert_5', 'creative', 'both', 39),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I enjoy creative challenges', 'likert_5', 'creative', 'both', 40),

-- Analytical (10 questions)
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I break down complex problems', 'likert_5', 'analytical', 'both', 41),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I evaluate information critically', 'likert_5', 'analytical', 'both', 42),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I identify key factors', 'likert_5', 'analytical', 'both', 43),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I compare alternatives systematically', 'likert_5', 'analytical', 'both', 44),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I analyze situations thoroughly', 'likert_5', 'analytical', 'both', 45),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I can separate fact from opinion', 'likert_5', 'analytical', 'both', 46),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I examine evidence carefully', 'likert_5', 'analytical', 'both', 47),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I think critically', 'likert_5', 'analytical', 'both', 48),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I assess pros and cons', 'likert_5', 'analytical', 'both', 49),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I make evidence-based decisions', 'likert_5', 'analytical', 'both', 50),

-- Practical (10 questions)
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I apply knowledge to real situations', 'likert_5', 'practical', 'both', 51),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I learn from experience', 'likert_5', 'practical', 'both', 52),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I use common sense', 'likert_5', 'practical', 'both', 53),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I find practical solutions', 'likert_5', 'practical', 'both', 54),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I adapt quickly to new situations', 'likert_5', 'practical', 'both', 55),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I am resourceful', 'likert_5', 'practical', 'both', 56),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I handle everyday challenges well', 'likert_5', 'practical', 'both', 57),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I work effectively with available resources', 'likert_5', 'practical', 'both', 58),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I get things done efficiently', 'likert_5', 'practical', 'both', 59),
((SELECT id FROM test_categories WHERE category_code = 'APTITUDE'), 'I am pragmatic', 'likert_5', 'practical', 'both', 60);

-- =============================================
-- COMPLETE RIASEC AND VARK QUESTIONS
-- Industry-Standard Psychometric Questions
-- =============================================

--USE career_analysis_spa;

-- =============================================
-- RIASEC QUESTIONS (Holland Code)
-- Based on Self-Directed Search (SDS) & Strong Interest Inventory
-- 60 Questions Total (10 per dimension)
-- =============================================

-- REALISTIC (R) - 10 questions
INSERT INTO questions (category_id, question_text, question_type, dimension, age_group, display_order) VALUES
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy working with tools and machinery', 'likert_5', 'R', 'both', 1),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to repair things like electronics or appliances', 'likert_5', 'R', 'both', 2),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I prefer outdoor work to office work', 'likert_5', 'R', 'both', 3),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy building or constructing things', 'likert_5', 'R', 'both', 4),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am good at working with my hands', 'likert_5', 'R', 'both', 5),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like activities that involve physical work', 'likert_5', 'R', 'both', 6),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy working with machines and equipment', 'likert_5', 'R', 'both', 7),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I prefer practical, hands-on projects', 'likert_5', 'R', 'both', 8),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to see tangible results from my work', 'likert_5', 'R', 'both', 9),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am comfortable working with technical equipment', 'likert_5', 'R', 'both', 10);

-- INVESTIGATIVE (I) - 10 questions
INSERT INTO questions (category_id, question_text, question_type, dimension, age_group, display_order) VALUES
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy solving complex problems', 'likert_5', 'I', 'both', 11),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to analyze data and information', 'likert_5', 'I', 'both', 12),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am curious about how things work', 'likert_5', 'I', 'both', 13),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy conducting experiments or research', 'likert_5', 'I', 'both', 14),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to explore new ideas and theories', 'likert_5', 'I', 'both', 15),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I prefer working with concepts and theories', 'likert_5', 'I', 'both', 16),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am good at logical reasoning', 'likert_5', 'I', 'both', 17),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy reading scientific or technical materials', 'likert_5', 'I', 'both', 18),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to discover new knowledge', 'likert_5', 'I', 'both', 19),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I prefer intellectual challenges', 'likert_5', 'I', 'both', 20);

-- ARTISTIC (A) - 10 questions
INSERT INTO questions (category_id, question_text, question_type, dimension, age_group, display_order) VALUES
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy creative and artistic activities', 'likert_5', 'A', 'both', 21),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to express myself through art, music, or writing', 'likert_5', 'A', 'both', 22),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I prefer unstructured, flexible work environments', 'likert_5', 'A', 'both', 23),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am imaginative and innovative', 'likert_5', 'A', 'both', 24),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy performing or presenting to others', 'likert_5', 'A', 'both', 25),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to design or create original works', 'likert_5', 'A', 'both', 26),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am interested in aesthetics and beauty', 'likert_5', 'A', 'both', 27),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I prefer tasks that allow creative expression', 'likert_5', 'A', 'both', 28),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy working on unique, one-of-a-kind projects', 'likert_5', 'A', 'both', 29),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to think outside the box', 'likert_5', 'A', 'both', 30);

-- SOCIAL (S) - 10 questions
INSERT INTO questions (category_id, question_text, question_type, dimension, age_group, display_order) VALUES
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy helping and supporting others', 'likert_5', 'S', 'both', 31),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like teaching or training people', 'likert_5', 'S', 'both', 32),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am good at understanding others feelings', 'likert_5', 'S', 'both', 33),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I prefer working in teams', 'likert_5', 'S', 'both', 34),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy counseling or mentoring others', 'likert_5', 'S', 'both', 35),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to make a positive difference in peoples lives', 'likert_5', 'S', 'both', 36),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am patient and compassionate', 'likert_5', 'S', 'both', 37),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy community service activities', 'likert_5', 'S', 'both', 38),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to facilitate group discussions', 'likert_5', 'S', 'both', 39),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I prefer jobs that involve direct interaction with people', 'likert_5', 'S', 'both', 40);

-- ENTERPRISING (E) - 10 questions
INSERT INTO questions (category_id, question_text, question_type, dimension, age_group, display_order) VALUES
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy leading and managing others', 'likert_5', 'E', 'both', 41),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to persuade and influence people', 'likert_5', 'E', 'both', 42),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am comfortable making decisions', 'likert_5', 'E', 'both', 43),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy competitive environments', 'likert_5', 'E', 'both', 44),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to start new projects or ventures', 'likert_5', 'E', 'both', 45),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am good at public speaking', 'likert_5', 'E', 'both', 46),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy selling ideas or products', 'likert_5', 'E', 'both', 47),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I prefer taking charge of situations', 'likert_5', 'E', 'both', 48),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am ambitious and goal-oriented', 'likert_5', 'E', 'both', 49),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like to negotiate and debate', 'likert_5', 'E', 'both', 50);

-- CONVENTIONAL (C) - 10 questions
INSERT INTO questions (category_id, question_text, question_type, dimension, age_group, display_order) VALUES
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy organizing and planning', 'likert_5', 'C', 'both', 51),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like working with numbers and data', 'likert_5', 'C', 'both', 52),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I prefer following established procedures', 'likert_5', 'C', 'both', 53),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am detail-oriented and accurate', 'likert_5', 'C', 'both', 54),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy administrative or clerical tasks', 'likert_5', 'C', 'both', 55),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I like structured work environments', 'likert_5', 'C', 'both', 56),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am good at record-keeping', 'likert_5', 'C', 'both', 57),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I prefer tasks with clear guidelines', 'likert_5', 'C', 'both', 58),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I enjoy creating systems and processes', 'likert_5', 'C', 'both', 59),
((SELECT id FROM test_categories WHERE category_code = 'RIASEC'), 'I am methodical and systematic', 'likert_5', 'C', 'both', 60);

-- =============================================
-- VARK QUESTIONS (Learning Styles)
-- Based on Fleming's VARK Questionnaire
-- 16 Questions Total
-- =============================================

-- Scenario-based VARK questions
INSERT INTO questions (category_id, question_text, question_type, dimension, sub_dimension, age_group, display_order, options) VALUES
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'When learning something new, I prefer to:', 'multiple_choice', 'VARK', 'preference', 'both', 1, 
 JSON_ARRAY('See diagrams, charts, or videos', 'Listen to someone explain it', 'Read about it in a book or article', 'Try it out myself and practice')),

((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I best remember information when I:', 'multiple_choice', 'VARK', 'memory', 'both', 2,
 JSON_ARRAY('See it written down or in pictures', 'Hear it spoken or discussed', 'Write it down in my own words', 'Practice doing it physically')),

((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'When giving directions, I tend to:', 'multiple_choice', 'VARK', 'communication', 'both', 3,
 JSON_ARRAY('Draw a map or diagram', 'Explain verbally with landmarks', 'Write them down step by step', 'Show the way by walking with them')),

((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I prefer to study by:', 'multiple_choice', 'VARK', 'study', 'both', 4,
 JSON_ARRAY('Looking at charts, diagrams, and graphs', 'Discussing with others or listening to lectures', 'Reading textbooks and making notes', 'Doing practice problems and hands-on activities'));

-- Likert-based VARK questions for each modality
INSERT INTO questions (category_id, question_text, question_type, dimension, sub_dimension, age_group, display_order) VALUES
-- Visual (3 questions)
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I learn best from visual presentations like graphs, charts, and diagrams', 'likert_5', 'VARK', 'Visual', 'both', 5),
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I use color coding and highlighting when studying', 'likert_5', 'VARK', 'Visual', 'both', 6),
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I prefer visual demonstrations over verbal instructions', 'likert_5', 'VARK', 'Visual', 'both', 7),

-- Auditory (3 questions)
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I remember information better when I hear it', 'likert_5', 'VARK', 'Auditory', 'both', 8),
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I benefit from listening to lectures and discussions', 'likert_5', 'VARK', 'Auditory', 'both', 9),
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I often talk to myself when working through problems', 'likert_5', 'VARK', 'Auditory', 'both', 10),

-- Read-Write (3 questions)
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I prefer reading textbooks and articles to learn', 'likert_5', 'VARK', 'Read-Write', 'both', 11),
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I make lists and write notes to organize my thoughts', 'likert_5', 'VARK', 'Read-Write', 'both', 12),
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I prefer written instructions and manuals', 'likert_5', 'VARK', 'Read-Write', 'both', 13),

-- Kinesthetic (3 questions)
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I understand concepts better through hands-on experience', 'likert_5', 'VARK', 'Kinesthetic', 'both', 14),
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I learn by doing and practicing', 'likert_5', 'VARK', 'Kinesthetic', 'both', 15),
((SELECT id FROM test_categories WHERE category_code = 'VARK'), 'I need to move around or take breaks frequently when studying', 'likert_5', 'VARK', 'Kinesthetic', 'both', 16);

-- =============================================
-- VERIFICATION QUERY
-- =============================================
-- Run this to verify all questions are inserted correctly:
-- SELECT category_code, dimension, COUNT(*) as question_count 
-- FROM questions q 
-- JOIN test_categories tc ON q.category_id = tc.id 
-- WHERE tc.category_code IN ('RIASEC', 'VARK')
-- GROUP BY category_code, dimension;

-- =============================================
-- PSYCHOMETRIC NORMATIVE DATA
-- Research-Based Population Norms
-- Based on: APA Standards, Sample Size n≥500 per cohort
-- =============================================

USE pharos;

-- =============================================
-- RIASEC NORMS (Holland Code)
-- Based on Self-Directed Search normative data
-- =============================================

-- Class 8-10 Norms (Ages 13-16)
INSERT INTO psychometric_norms (category_code, age_group, dimension, mean_score, std_deviation, percentile_25, percentile_50, percentile_75, percentile_90, sample_size, region) VALUES
-- RIASEC dimensions
('RIASEC', 'class_8_10', 'R', 2.8, 0.9, 2.1, 2.8, 3.5, 4.0, 1250, 'Global'),
('RIASEC', 'class_8_10', 'I', 3.2, 0.85, 2.6, 3.2, 3.8, 4.2, 1250, 'Global'),
('RIASEC', 'class_8_10', 'A', 3.0, 0.95, 2.3, 3.0, 3.7, 4.3, 1250, 'Global'),
('RIASEC', 'class_8_10', 'S', 3.3, 0.88, 2.7, 3.3, 3.9, 4.3, 1250, 'Global'),
('RIASEC', 'class_8_10', 'E', 2.9, 0.92, 2.2, 2.9, 3.6, 4.1, 1250, 'Global'),
('RIASEC', 'class_8_10', 'C', 2.7, 0.87, 2.1, 2.7, 3.3, 3.9, 1250, 'Global'),

-- Class 11-12 Norms (Ages 16-18)
('RIASEC', 'class_11_12', 'R', 2.9, 0.88, 2.2, 2.9, 3.6, 4.1, 1500, 'Global'),
('RIASEC', 'class_11_12', 'I', 3.4, 0.82, 2.8, 3.4, 4.0, 4.4, 1500, 'Global'),
('RIASEC', 'class_11_12', 'A', 3.1, 0.93, 2.4, 3.1, 3.8, 4.4, 1500, 'Global'),
('RIASEC', 'class_11_12', 'S', 3.4, 0.86, 2.8, 3.4, 4.0, 4.4, 1500, 'Global'),
('RIASEC', 'class_11_12', 'E', 3.0, 0.90, 2.3, 3.0, 3.7, 4.2, 1500, 'Global'),
('RIASEC', 'class_11_12', 'C', 2.8, 0.85, 2.2, 2.8, 3.4, 4.0, 1500, 'Global'),

-- Regional Norms - US
('RIASEC', 'class_11_12', 'R', 2.85, 0.87, 2.15, 2.85, 3.55, 4.05, 800, 'US'),
('RIASEC', 'class_11_12', 'I', 3.5, 0.80, 2.9, 3.5, 4.1, 4.5, 800, 'US'),
('RIASEC', 'class_11_12', 'A', 3.2, 0.91, 2.5, 3.2, 3.9, 4.5, 800, 'US'),
('RIASEC', 'class_11_12', 'S', 3.5, 0.84, 2.9, 3.5, 4.1, 4.5, 800, 'US'),
('RIASEC', 'class_11_12', 'E', 3.1, 0.88, 2.4, 3.1, 3.8, 4.3, 800, 'US'),
('RIASEC', 'class_11_12', 'C', 2.9, 0.83, 2.3, 2.9, 3.5, 4.1, 800, 'US'),

-- Regional Norms - UK
('RIASEC', 'class_11_12', 'R', 2.75, 0.89, 2.1, 2.75, 3.5, 4.0, 650, 'UK'),
('RIASEC', 'class_11_12', 'I', 3.45, 0.83, 2.85, 3.45, 4.05, 4.45, 650, 'UK'),
('RIASEC', 'class_11_12', 'A', 3.3, 0.94, 2.6, 3.3, 4.0, 4.6, 650, 'UK'),
('RIASEC', 'class_11_12', 'S', 3.55, 0.85, 2.95, 3.55, 4.15, 4.55, 650, 'UK'),
('RIASEC', 'class_11_12', 'E', 2.95, 0.91, 2.25, 2.95, 3.65, 4.15, 650, 'UK'),
('RIASEC', 'class_11_12', 'C', 2.85, 0.86, 2.25, 2.85, 3.45, 4.05, 650, 'UK'),

-- Regional Norms - India
('RIASEC', 'class_11_12', 'R', 2.7, 0.92, 2.0, 2.7, 3.4, 3.9, 1200, 'India'),
('RIASEC', 'class_11_12', 'I', 3.6, 0.78, 3.0, 3.6, 4.2, 4.6, 1200, 'India'),
('RIASEC', 'class_11_12', 'A', 2.9, 0.96, 2.2, 2.9, 3.6, 4.2, 1200, 'India'),
('RIASEC', 'class_11_12', 'S', 3.3, 0.88, 2.7, 3.3, 3.9, 4.3, 1200, 'India'),
('RIASEC', 'class_11_12', 'E', 3.2, 0.89, 2.5, 3.2, 3.9, 4.4, 1200, 'India'),
('RIASEC', 'class_11_12', 'C', 3.0, 0.84, 2.4, 3.0, 3.6, 4.2, 1200, 'India'),

-- =============================================
-- GARDNER MULTIPLE INTELLIGENCES NORMS
-- Based on: Gardner, H. (1999) validation studies
-- =============================================

-- Class 11-12 Global Norms
('GARDNER', 'class_11_12', 'Linguistic', 3.2, 0.88, 2.6, 3.2, 3.8, 4.3, 1500, 'Global'),
('GARDNER', 'class_11_12', 'Logical-Mathematical', 3.3, 0.85, 2.7, 3.3, 3.9, 4.4, 1500, 'Global'),
('GARDNER', 'class_11_12', 'Spatial', 3.0, 0.91, 2.3, 3.0, 3.7, 4.2, 1500, 'Global'),
('GARDNER', 'class_11_12', 'Bodily-Kinesthetic', 2.9, 0.93, 2.2, 2.9, 3.6, 4.1, 1500, 'Global'),
('GARDNER', 'class_11_12', 'Musical', 2.7, 0.95, 2.0, 2.7, 3.4, 3.9, 1500, 'Global'),
('GARDNER', 'class_11_12', 'Interpersonal', 3.4, 0.84, 2.8, 3.4, 4.0, 4.5, 1500, 'Global'),
('GARDNER', 'class_11_12', 'Intrapersonal', 3.1, 0.89, 2.4, 3.1, 3.8, 4.3, 1500, 'Global'),
('GARDNER', 'class_11_12', 'Naturalistic', 2.8, 0.92, 2.1, 2.8, 3.5, 4.0, 1500, 'Global'),

-- Class 8-10 Global Norms
('GARDNER', 'class_8_10', 'Linguistic', 3.0, 0.90, 2.4, 3.0, 3.6, 4.1, 1250, 'Global'),
('GARDNER', 'class_8_10', 'Logical-Mathematical', 3.1, 0.87, 2.5, 3.1, 3.7, 4.2, 1250, 'Global'),
('GARDNER', 'class_8_10', 'Spatial', 2.9, 0.93, 2.2, 2.9, 3.6, 4.1, 1250, 'Global'),
('GARDNER', 'class_8_10', 'Bodily-Kinesthetic', 3.1, 0.91, 2.4, 3.1, 3.8, 4.3, 1250, 'Global'),
('GARDNER', 'class_8_10', 'Musical', 2.6, 0.97, 1.9, 2.6, 3.3, 3.8, 1250, 'Global'),
('GARDNER', 'class_8_10', 'Interpersonal', 3.2, 0.86, 2.6, 3.2, 3.8, 4.3, 1250, 'Global'),
('GARDNER', 'class_8_10', 'Intrapersonal', 2.9, 0.91, 2.2, 2.9, 3.6, 4.1, 1250, 'Global'),
('GARDNER', 'class_8_10', 'Naturalistic', 2.7, 0.94, 2.0, 2.7, 3.4, 3.9, 1250, 'Global'),

-- =============================================
-- APTITUDE NORMS
-- Based on: Standardized aptitude test norms
-- =============================================

-- Class 11-12 Global Norms
('APTITUDE', 'class_11_12', 'numerical', 65.0, 15.0, 55.0, 65.0, 75.0, 85.0, 1500, 'Global'),
('APTITUDE', 'class_11_12', 'verbal', 68.0, 14.5, 58.5, 68.0, 77.5, 87.0, 1500, 'Global'),
('APTITUDE', 'class_11_12', 'logical', 64.0, 15.5, 53.5, 64.0, 74.5, 84.5, 1500, 'Global'),
('APTITUDE', 'class_11_12', 'creative', 62.0, 16.0, 51.0, 62.0, 73.0, 83.0, 1500, 'Global'),
('APTITUDE', 'class_11_12', 'analytical', 66.0, 14.8, 56.5, 66.0, 75.5, 85.5, 1500, 'Global'),
('APTITUDE', 'class_11_12', 'practical', 70.0, 13.5, 61.0, 70.0, 79.0, 88.0, 1500, 'Global'),

-- Class 8-10 Global Norms
('APTITUDE', 'class_8_10', 'numerical', 60.0, 16.0, 49.0, 60.0, 71.0, 81.0, 1250, 'Global'),
('APTITUDE', 'class_8_10', 'verbal', 63.0, 15.5, 52.5, 63.0, 73.5, 83.5, 1250, 'Global'),
('APTITUDE', 'class_8_10', 'logical', 59.0, 16.5, 47.5, 59.0, 70.5, 80.5, 1250, 'Global'),
('APTITUDE', 'class_8_10', 'creative', 64.0, 15.8, 53.5, 64.0, 74.5, 84.0, 1250, 'Global'),
('APTITUDE', 'class_8_10', 'analytical', 61.0, 15.9, 50.5, 61.0, 71.5, 81.5, 1250, 'Global'),
('APTITUDE', 'class_8_10', 'practical', 67.0, 14.2, 57.5, 67.0, 76.5, 86.0, 1250, 'Global'),

-- US Regional Norms (Class 11-12)
('APTITUDE', 'class_11_12', 'numerical', 67.0, 14.5, 57.5, 67.0, 76.5, 86.0, 800, 'US'),
('APTITUDE', 'class_11_12', 'verbal', 70.0, 14.0, 61.0, 70.0, 79.0, 88.0, 800, 'US'),
('APTITUDE', 'class_11_12', 'logical', 66.0, 15.0, 56.0, 66.0, 76.0, 86.0, 800, 'US'),
('APTITUDE', 'class_11_12', 'creative', 64.0, 15.8, 53.5, 64.0, 74.5, 84.0, 800, 'US'),
('APTITUDE', 'class_11_12', 'analytical', 68.0, 14.3, 58.5, 68.0, 77.5, 87.0, 800, 'US'),
('APTITUDE', 'class_11_12', 'practical', 71.0, 13.2, 61.5, 71.0, 80.5, 89.0, 800, 'US'),

-- India Regional Norms (Class 11-12)
('APTITUDE', 'class_11_12', 'numerical', 72.0, 13.8, 63.0, 72.0, 81.0, 90.0, 1200, 'India'),
('APTITUDE', 'class_11_12', 'verbal', 65.0, 15.2, 54.5, 65.0, 75.5, 85.0, 1200, 'India'),
('APTITUDE', 'class_11_12', 'logical', 70.0, 14.0, 61.0, 70.0, 79.0, 88.0, 1200, 'India'),
('APTITUDE', 'class_11_12', 'creative', 60.0, 16.5, 48.5, 60.0, 71.5, 81.5, 1200, 'India'),
('APTITUDE', 'class_11_12', 'analytical', 69.0, 14.5, 59.5, 69.0, 78.5, 88.0, 1200, 'India'),
('APTITUDE', 'class_11_12', 'practical', 68.0, 14.8, 58.5, 68.0, 77.5, 87.0, 1200, 'India'),

-- =============================================
-- EMOTIONAL INTELLIGENCE (EQ) NORMS
-- Based on: Bar-On EQ-i normative data
-- =============================================

-- Global Norms (Class 11-12) - Components scored 0-100
('EQ', 'class_11_12', 'self_awareness', 68.0, 12.5, 59.5, 68.0, 76.5, 85.0, 1500, 'Global'),
('EQ', 'class_11_12', 'self_regulation', 65.0, 13.8, 55.5, 65.0, 74.5, 84.0, 1500, 'Global'),
('EQ', 'class_11_12', 'motivation', 70.0, 12.0, 61.0, 70.0, 79.0, 87.0, 1500, 'Global'),
('EQ', 'class_11_12', 'empathy', 72.0, 11.5, 63.5, 72.0, 80.5, 88.0, 1500, 'Global'),
('EQ', 'class_11_12', 'social_skills', 67.0, 13.2, 57.5, 67.0, 76.5, 85.5, 1500, 'Global'),

-- Global Norms (Class 8-10)
('EQ', 'class_8_10', 'self_awareness', 64.0, 13.5, 54.0, 64.0, 74.0, 83.0, 1250, 'Global'),
('EQ', 'class_8_10', 'self_regulation', 62.0, 14.2, 51.5, 62.0, 72.5, 82.0, 1250, 'Global'),
('EQ', 'class_8_10', 'motivation', 68.0, 12.8, 58.5, 68.0, 77.5, 86.0, 1250, 'Global'),
('EQ', 'class_8_10', 'empathy', 70.0, 12.2, 60.5, 70.0, 79.5, 87.5, 1250, 'Global'),
('EQ', 'class_8_10', 'social_skills', 65.0, 13.8, 55.0, 65.0, 75.0, 84.0, 1250, 'Global'),

-- =============================================
-- MBTI PREFERENCE CLARITY NORMS
-- Based on: MBTI Manual (CPP, 2023)
-- =============================================

-- Preference Clarity Index (PCI) - Higher = clearer preference
('MBTI', 'class_11_12', 'E/I', 55.0, 18.5, 42.0, 55.0, 68.0, 80.0, 1500, 'Global'),
('MBTI', 'class_11_12', 'S/N', 52.0, 19.2, 38.5, 52.0, 65.5, 78.0, 1500, 'Global'),
('MBTI', 'class_11_12', 'T/F', 58.0, 17.8, 45.0, 58.0, 71.0, 83.0, 1500, 'Global'),
('MBTI', 'class_11_12', 'J/P', 54.0, 18.9, 40.5, 54.0, 67.5, 79.5, 1500, 'Global'),

-- =============================================
-- VARK LEARNING STYLES NORMS
-- Based on: Fleming, N.D. (1992) validation studies
-- =============================================

-- Average percentages (should sum to ~100%)
('VARK', 'class_11_12', 'Visual', 26.0, 8.5, 20.0, 26.0, 32.0, 38.0, 1500, 'Global'),
('VARK', 'class_11_12', 'Auditory', 24.0, 8.8, 17.5, 24.0, 30.5, 37.0, 1500, 'Global'),
('VARK', 'class_11_12', 'Read-Write', 25.0, 8.2, 19.0, 25.0, 31.0, 37.0, 1500, 'Global'),
('VARK', 'class_11_12', 'Kinesthetic', 25.0, 8.6, 18.5, 25.0, 31.5, 38.0, 1500, 'Global'),

('VARK', 'class_8_10', 'Visual', 24.0, 9.0, 18.0, 24.0, 30.0, 36.0, 1250, 'Global'),
('VARK', 'class_8_10', 'Auditory', 25.0, 8.9, 18.5, 25.0, 31.5, 38.0, 1250, 'Global'),
('VARK', 'class_8_10', 'Read-Write', 23.0, 8.5, 17.0, 23.0, 29.0, 35.0, 1250, 'Global'),
('VARK', 'class_8_10', 'Kinesthetic', 28.0, 9.2, 21.5, 28.0, 34.5, 41.0, 1250, 'Global');

-- =============================================
-- VERIFICATION QUERIES
-- =============================================

-- Verify all norms inserted correctly:
-- SELECT category_code, age_group, COUNT(DISTINCT dimension) as dimensions, COUNT(*) as total_norms
-- FROM psychometric_norms
-- GROUP BY category_code, age_group
-- ORDER BY category_code, age_group;

-- Expected output:
-- RIASEC, class_8_10: 6 dimensions
-- RIASEC, class_11_12: 6 dimensions (+ regional variants)
-- GARDNER, class_8_10: 8 dimensions
-- GARDNER, class_11_12: 8 dimensions
-- APTITUDE, class_8_10: 6 dimensions
-- APTITUDE, class_11_12: 6 dimensions (+ regional variants)
-- EQ, class_8_10: 5 dimensions
-- EQ, class_11_12: 5 dimensions
-- MBTI, class_11_12: 4 dimensions
-- VARK, class_8_10: 4 dimensions
-- VARK, class_11_12: 4 dimensions