<?php

/**
 * Predefined skills catalog for the profile builder's autocomplete/chip
 * component - purely a suggestion source, students can always type a
 * custom skill that isn't in this list. Grouped by category for anyone
 * extending it; the flat list is what actually gets sent to the browser.
 * Add new skills by adding to the relevant category array below.
 */

$categories = [
    'Programming Languages' => ['Java', 'Python', 'C', 'C++', 'C#', 'JavaScript', 'TypeScript', 'PHP', 'Kotlin', 'Swift', 'Go', 'Rust', 'R', 'Dart', 'MATLAB', 'Scala'],
    'Frontend' => ['HTML', 'CSS', 'Bootstrap', 'Tailwind CSS', 'React', 'Angular', 'Vue.js', 'Next.js'],
    'Backend' => ['Node.js', 'Express.js', 'Laravel', 'Django', 'Flask', 'Spring Boot', 'ASP.NET'],
    'Databases' => ['MySQL', 'PostgreSQL', 'MongoDB', 'SQLite', 'Oracle', 'Firebase', 'Redis'],
    'Cloud' => ['AWS', 'Microsoft Azure', 'Google Cloud', 'Cloud Computing'],
    'DevOps' => ['Docker', 'Kubernetes', 'Jenkins', 'GitHub Actions', 'CI/CD', 'Linux'],
    'AI/ML' => ['Machine Learning', 'Deep Learning', 'Artificial Intelligence', 'TensorFlow', 'PyTorch', 'Scikit-learn', 'NLP', 'Computer Vision'],
    'Data' => ['Data Science', 'Data Analytics', 'Power BI', 'Tableau', 'Excel'],
    'Mobile' => ['Android', 'Android Studio', 'Flutter', 'React Native'],
    'Tools' => ['Git', 'GitHub', 'GitLab', 'VS Code', 'IntelliJ IDEA', 'Postman'],
    'Cybersecurity' => ['Cyber Security', 'Network Security', 'Ethical Hacking', 'Penetration Testing'],
    'IoT/Embedded' => ['Arduino', 'ESP32', 'Raspberry Pi', 'IoT', 'Embedded Systems'],
    'Soft Skills' => ['Communication', 'Leadership', 'Teamwork', 'Problem Solving', 'Time Management', 'Presentation Skills', 'Critical Thinking'],
];

// Related-skill hints shown once a skill is added (client-side only,
// never auto-added) - keyed by lowercase skill name.
$suggestions = [
    'java' => ['Spring Boot', 'Hibernate', 'Maven', 'MySQL', 'Git'],
    'python' => ['Django', 'Flask', 'Machine Learning', 'Pandas', 'NumPy'],
    'react' => ['JavaScript', 'TypeScript', 'Node.js', 'Next.js'],
    'javascript' => ['TypeScript', 'React', 'Node.js', 'HTML', 'CSS'],
    'html' => ['CSS', 'Bootstrap', 'JavaScript'],
    'node.js' => ['Express.js', 'MongoDB', 'JavaScript'],
    'django' => ['Python', 'PostgreSQL', 'REST APIs'],
    'spring boot' => ['Java', 'Hibernate', 'MySQL', 'Maven'],
    'android' => ['Kotlin', 'Java', 'Android Studio', 'Firebase'],
    'machine learning' => ['Python', 'TensorFlow', 'PyTorch', 'Scikit-learn', 'Pandas'],
    'docker' => ['Kubernetes', 'CI/CD', 'Linux'],
    'aws' => ['Cloud Computing', 'Docker', 'Linux'],
    'mysql' => ['PostgreSQL', 'SQL', 'Database Design'],
    'flutter' => ['Dart', 'Firebase', 'Android'],
];

$flat = [];
foreach ($categories as $skills) {
    $flat = array_merge($flat, $skills);
}

return [
    'categories' => $categories,
    'flat' => array_values(array_unique($flat)),
    'suggestions' => $suggestions,
];
