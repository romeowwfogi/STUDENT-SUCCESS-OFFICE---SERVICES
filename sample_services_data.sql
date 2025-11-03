-- Sample data for services_list table
-- This file contains sample services to test the dynamic services functionality
-- Now includes both Lucide icon names and SVG content examples

-- First, create the table if it doesn't exist (from dynamic_services.md)
CREATE TABLE IF NOT EXISTS services_list (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    icon TEXT NULL,
    button_text TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT 1
);

-- Insert sample services data with both Lucide icons and SVG examples
INSERT INTO services_list (name, description, icon, button_text, is_active) VALUES
('ID Replacement', 'Lost or damaged your student ID? Get a new one quickly with our streamlined replacement process. Available in digital and physical formats.', 'credit-card', 'Request Now', 1),

('Good Moral Certificate', 'Official certificate of good moral character for your applications. Accepted by employers and institutions with secure verification.', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c.552 0 1-.448 1-1V5c0-.552-.448-1-1-1H3c-.552 0-1 .448-1 1v6c0 .552.448 1 1 1h18z"/><path d="M3 12v6c0 .552.448 1 1 1h16c.552 0 1-.448 1-1v-6"/></svg>', 'Request Now', 1),

('Academic Transcript', 'Request official academic transcripts for job applications, transfers, or graduate school. Secure and verified documentation.', 'file-text', 'Request Now', 1),

('Enrollment Certificate', 'Official certificate verifying your current enrollment status. Required for various applications and benefits.', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m22 2-5 10-5-4-5 10"/><path d="m16 12 5-10"/></svg>', 'Request Now', 0),

('Scholarship Application', 'Apply for various scholarship programs and financial aid opportunities. Get guidance through the application process.', 'award', 'Apply Now', 1),

('Library Card', 'Get access to library resources, digital collections, and study materials. Essential for academic research and learning.', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>', 'Get Access', 0),

('Graduation Clearance', 'Complete your graduation requirements checklist and get clearance for your diploma. Track your progress online.', 'graduation-cap', 'Start Process', 1),

('Student Handbook', 'Access the complete student handbook with policies, procedures, and important information for your academic journey.', 'book', 'Download', 1),

('Counseling Services', 'Professional counseling and mental health support services. Schedule appointments with our qualified counselors.', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7z"/></svg>', 'Book Session', 1),

('Internship Program', 'Find and apply for internship opportunities that align with your career goals. Get real-world experience.', 'briefcase', 'Explore', 1);

-- You can run this SQL to populate your services_list table with sample data
-- After running this, your dynamic services page should display these services
-- Some services use Lucide icon names (like 'credit-card', 'award') 
-- Others use custom SVG content for more specific icons