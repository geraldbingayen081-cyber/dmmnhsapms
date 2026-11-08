const fs = require('fs');
const path = require('path');

// Table definitions with accurate fields, types, and constraints
const tables = {
    users: {
        x: 50, y: 140, w: 320,
        name: 'users',
        title: 'users',
        stereotype: '<<identity: core>>',
        headerBg: '#2563eb',
        badgeColor: '#1d4ed8',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'UK', name: 'login_id', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'name', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'email', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'password', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'role', type: "ENUM('admin','teacher','student','parent')", null: false },
            { key: '', name: 'status', type: "ENUM('active','inactive')", null: false },
            { key: '', name: 'profile_photo_path', type: 'VARCHAR(2048)', null: true },
            { key: '', name: 'remember_token', type: 'VARCHAR(100)', null: true },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    teachers: {
        x: 440, y: 140, w: 320,
        name: 'teachers',
        title: 'teachers',
        stereotype: '<<profile: faculty>>',
        headerBg: '#0284c7',
        badgeColor: '#0369a1',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'FK,UK', name: 'user_id', type: 'BIGINT UNSIGNED', null: false, ref: 'users.id' },
            { key: 'UK', name: 'employee_number', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'first_name', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'middle_name', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'last_name', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'suffix', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'contact_number', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    students: {
        x: 440, y: 440, w: 320,
        name: 'students',
        title: 'students',
        stereotype: '<<profile: learner>>',
        headerBg: '#0284c7',
        badgeColor: '#0369a1',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'FK,UK', name: 'user_id', type: 'BIGINT UNSIGNED', null: false, ref: 'users.id' },
            { key: 'UK', name: 'student_number', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'first_name', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'middle_name', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'last_name', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'suffix', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'gender', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'birthdate', type: 'DATE', null: true },
            { key: '', name: 'contact_number', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    parents: {
        x: 50, y: 460, w: 320,
        name: 'parents',
        title: 'parents',
        stereotype: '<<profile: guardian>>',
        headerBg: '#0284c7',
        badgeColor: '#0369a1',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'FK,UK', name: 'user_id', type: 'BIGINT UNSIGNED', null: false, ref: 'users.id' },
            { key: '', name: 'first_name', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'middle_name', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'last_name', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'suffix', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'contact_number', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    parent_student: {
        x: 50, y: 730, w: 320,
        name: 'parent_student',
        title: 'parent_student',
        stereotype: '<<associative: pivot>>',
        headerBg: '#0891b2',
        badgeColor: '#0e7490',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'FK', name: 'parent_id', type: 'BIGINT UNSIGNED', null: false, ref: 'parents.id' },
            { key: 'FK', name: 'student_id', type: 'BIGINT UNSIGNED', null: false, ref: 'students.id' },
            { key: '', name: 'relationship', type: "VARCHAR(255) DEFAULT 'Guardian'", null: false },
            { key: 'UK', name: '(parent_id, student_id)', type: 'COMPOSITE UNIQUE', null: false },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    activity_logs: {
        x: 50, y: 960, w: 320,
        name: 'activity_logs',
        title: 'activity_logs',
        stereotype: '<<security: audit trail>>',
        headerBg: '#475569',
        badgeColor: '#334155',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'FK', name: 'user_id', type: 'BIGINT UNSIGNED', null: true, ref: 'users.id' },
            { key: '', name: 'action', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'subject_type', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'subject_id', type: 'BIGINT UNSIGNED', null: true },
            { key: '', name: 'description', type: 'TEXT', null: true },
            { key: '', name: 'old_values', type: 'JSON', null: true },
            { key: '', name: 'new_values', type: 'JSON', null: true },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    school_years: {
        x: 830, y: 140, w: 320,
        name: 'school_years',
        title: 'school_years',
        stereotype: '<<calendar: academic period>>',
        headerBg: '#7c3aed',
        badgeColor: '#6d28d9',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'UK', name: 'school_year', type: "VARCHAR(255) e.g. '2026-2027'", null: false },
            { key: '', name: 'start_date', type: 'DATE', null: false },
            { key: '', name: 'end_date', type: 'DATE', null: false },
            { key: '', name: 'status', type: "ENUM('draft','active','completed','archived')", null: false },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    quarters: {
        x: 1220, y: 140, w: 320,
        name: 'quarters',
        title: 'quarters',
        stereotype: '<<calendar: grading term>>',
        headerBg: '#7c3aed',
        badgeColor: '#6d28d9',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'FK', name: 'school_year_id', type: 'BIGINT UNSIGNED', null: false, ref: 'school_years.id' },
            { key: '', name: 'name', type: "VARCHAR(255) e.g. '1st Quarter'", null: false },
            { key: '', name: 'short_name', type: "VARCHAR(255) e.g. 'Q1'", null: false },
            { key: '', name: 'sort_order', type: 'INT', null: false },
            { key: '', name: 'start_date', type: 'DATE', null: true },
            { key: '', name: 'end_date', type: 'DATE', null: true },
            { key: '', name: 'status', type: "ENUM('upcoming','active','completed')", null: false },
            { key: 'UK', name: '(school_year_id, sort_order)', type: 'COMPOSITE UNIQUE', null: false },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    year_levels: {
        x: 1220, y: 440, w: 320,
        name: 'year_levels',
        title: 'year_levels',
        stereotype: '<<curriculum: grade level>>',
        headerBg: '#9333ea',
        badgeColor: '#7e22ce',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: '', name: 'name', type: "VARCHAR(255) e.g. 'Grade 7'", null: false },
            { key: 'UK', name: 'code', type: "VARCHAR(255) e.g. 'G7'", null: false },
            { key: '', name: 'sort_order', type: 'INT', null: false },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    sections: {
        x: 1220, y: 620, w: 320,
        name: 'sections',
        title: 'sections',
        stereotype: '<<structure: section catalog>>',
        headerBg: '#9333ea',
        badgeColor: '#7e22ce',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'UK', name: 'name', type: "VARCHAR(255) e.g. 'Rizal'", null: false },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    subjects: {
        x: 1220, y: 760, w: 320,
        name: 'subjects',
        title: 'subjects',
        stereotype: '<<curriculum: learning area>>',
        headerBg: '#9333ea',
        badgeColor: '#7e22ce',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'UK', name: 'subject_code', type: "VARCHAR(255) e.g. 'MATH'", null: false },
            { key: '', name: 'subject_name', type: "VARCHAR(255) e.g. 'Mathematics'", null: false },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    school_year_sections: {
        x: 830, y: 400, w: 320,
        name: 'school_year_sections',
        title: 'school_year_sections',
        stereotype: '<<structure: class offering>>',
        headerBg: '#d97706',
        badgeColor: '#b45309',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'FK', name: 'school_year_id', type: 'BIGINT UNSIGNED', null: false, ref: 'school_years.id' },
            { key: 'FK', name: 'section_id', type: 'BIGINT UNSIGNED', null: false, ref: 'sections.id' },
            { key: 'FK', name: 'year_level_id', type: 'BIGINT UNSIGNED', null: false, ref: 'year_levels.id' },
            { key: 'FK', name: 'adviser_id', type: 'BIGINT UNSIGNED', null: true, ref: 'teachers.id' },
            { key: '', name: 'status', type: "ENUM('active','inactive')", null: false },
            { key: 'UK', name: '(school_year_id, yl_id, sec_id)', type: 'COMPOSITE UNIQUE', null: false },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    student_enrollments: {
        x: 440, y: 770, w: 320,
        name: 'student_enrollments',
        title: 'student_enrollments',
        stereotype: '<<enrollment: academic roster>>',
        headerBg: '#d97706',
        badgeColor: '#b45309',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'FK', name: 'student_id', type: 'BIGINT UNSIGNED', null: false, ref: 'students.id' },
            { key: 'FK', name: 'school_year_section_id', type: 'BIGINT UNSIGNED', null: false, ref: 'school_year_sections.id' },
            { key: '', name: 'status', type: "ENUM('enrolled','promoted','retained',...)", null: false },
            { key: '', name: 'enrollment_date', type: 'DATE', null: false },
            { key: '', name: 'completion_date', type: 'DATE', null: true },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    section_subjects: {
        x: 830, y: 700, w: 320,
        name: 'section_subjects',
        title: 'section_subjects',
        stereotype: '<<assignment: teacher load>>',
        headerBg: '#d97706',
        badgeColor: '#b45309',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'FK', name: 'school_year_section_id', type: 'BIGINT UNSIGNED', null: false, ref: 'school_year_sections.id' },
            { key: 'FK', name: 'subject_id', type: 'BIGINT UNSIGNED', null: false, ref: 'subjects.id' },
            { key: 'FK', name: 'teacher_id', type: 'BIGINT UNSIGNED', null: true, ref: 'teachers.id' },
            { key: 'UK', name: '(sy_section_id, subject_id)', type: 'COMPOSITE UNIQUE', null: false },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    grades: {
        x: 650, y: 1010, w: 360,
        name: 'grades',
        title: 'grades',
        stereotype: '<<assessment: DepEd evaluation>>',
        headerBg: '#059669',
        badgeColor: '#047857',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'FK', name: 'student_enrollment_id', type: 'BIGINT UNSIGNED', null: false, ref: 'student_enrollments.id' },
            { key: 'FK', name: 'section_subject_id', type: 'BIGINT UNSIGNED', null: false, ref: 'section_subjects.id' },
            { key: 'FK', name: 'quarter_id', type: 'BIGINT UNSIGNED', null: false, ref: 'quarters.id' },
            { key: '', name: 'grade', type: 'DECIMAL(5,2)', null: true },
            { key: '', name: 'remarks', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'status', type: "ENUM('draft','approved','locked')", null: false },
            { key: 'FK', name: 'created_by', type: 'BIGINT UNSIGNED', null: true, ref: 'users.id' },
            { key: 'UK', name: '(enrollment, subject, quarter)', type: 'COMPOSITE UNIQUE', null: false },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    achievement_rules: {
        x: 1080, y: 980, w: 300,
        name: 'achievement_rules',
        title: 'achievement_rules',
        stereotype: '<<honors: DepEd policy criteria>>',
        headerBg: '#db2777',
        badgeColor: '#be185d',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'FK', name: 'school_year_id', type: 'BIGINT UNSIGNED', null: false, ref: 'school_years.id' },
            { key: '', name: 'name', type: "VARCHAR(255) e.g. 'With Honors'", null: false },
            { key: '', name: 'type', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'minimum_average', type: 'DECIMAL(5,2)', null: true },
            { key: '', name: 'maximum_average', type: 'DECIMAL(5,2)', null: true },
            { key: '', name: 'minimum_subject_grade', type: 'DECIMAL(5,2)', null: true },
            { key: '', name: 'criteria', type: 'JSON', null: true },
            { key: '', name: 'is_active', type: 'BOOLEAN DEFAULT TRUE', null: false },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    achievements: {
        x: 1410, y: 980, w: 300,
        name: 'achievements',
        title: 'achievements',
        stereotype: '<<honors: awarded recognition>>',
        headerBg: '#db2777',
        badgeColor: '#be185d',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'FK', name: 'student_id', type: 'BIGINT UNSIGNED', null: false, ref: 'students.id' },
            { key: 'FK', name: 'school_year_id', type: 'BIGINT UNSIGNED', null: true, ref: 'school_years.id' },
            { key: '', name: 'type', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'title', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'quarter', type: 'VARCHAR(255)', null: true },
            { key: '', name: 'description', type: 'TEXT', null: true },
            { key: '', name: 'remarks', type: 'TEXT', null: true },
            { key: '', name: 'date_awarded', type: 'DATE', null: true },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    },
    system_settings: {
        x: 1180, y: 1300, w: 340,
        name: 'system_settings',
        title: 'system_settings',
        stereotype: '<<configuration: key-value>>',
        headerBg: '#475569',
        badgeColor: '#334155',
        columns: [
            { key: 'PK', name: 'id', type: 'BIGINT UNSIGNED', null: false },
            { key: 'UK', name: 'key', type: 'VARCHAR(255)', null: false },
            { key: '', name: 'value', type: 'TEXT', null: true },
            { key: '', name: 'created_at / updated_at', type: 'TIMESTAMP', null: true }
        ]
    }
};

// Calculate height for each table box
const ROW_H = 21;
const HEADER_H = 46;

for (const t of Object.values(tables)) {
    t.h = HEADER_H + (t.columns.length * ROW_H) + 10;
}

// SVG generation function
function renderSVG() {
    const W = 1780;
    const H = 1580;

    let svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg width="${W}" height="${H}" viewBox="0 0 ${W} ${H}" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <!-- Shadow filters -->
        <filter id="erd-shadow" x="-5%" y="-5%" width="110%" height="110%" filterUnits="userSpaceOnUse">
            <feDropShadow dx="2" dy="4" stdDeviation="3.5" flood-color="#0f172a" flood-opacity="0.12"/>
        </filter>
        <filter id="erd-glow" x="-10%" y="-10%" width="120%" height="120%" filterUnits="userSpaceOnUse">
            <feDropShadow dx="0" dy="0" stdDeviation="4" flood-color="#3b82f6" flood-opacity="0.3"/>
        </filter>

        <!-- Crow's foot / Cardinality Markers -->
        <!-- 1-to-1: two perpendicular bars -->
        <marker id="card-one" viewBox="0 0 16 16" refX="14" refY="8" markerWidth="14" markerHeight="14" orient="auto">
            <line x1="8" y1="2" x2="8" y2="14" stroke="#475569" stroke-width="2.2"/>
            <line x1="13" y1="2" x2="13" y2="14" stroke="#475569" stroke-width="2.2"/>
        </marker>

        <!-- 1-to-many: Crow's Foot with inner bar -->
        <marker id="card-many" viewBox="0 0 20 16" refX="18" refY="8" markerWidth="16" markerHeight="14" orient="auto">
            <line x1="6" y1="2" x2="6" y2="14" stroke="#475569" stroke-width="2"/>
            <path d="M 18 2 L 6 8 L 18 14" fill="none" stroke="#475569" stroke-width="2.2" stroke-linecap="round"/>
        </marker>

        <!-- Zero-to-many: Crow's Foot with circle -->
        <marker id="card-zero-many" viewBox="0 0 24 16" refX="22" refY="8" markerWidth="18" markerHeight="14" orient="auto">
            <circle cx="6" cy="8" r="4.5" fill="#ffffff" stroke="#475569" stroke-width="1.8"/>
            <path d="M 22 2 L 12 8 L 22 14" fill="none" stroke="#475569" stroke-width="2.2" stroke-linecap="round"/>
        </marker>

        <!-- Arrowhead for general foreign key dependency -->
        <marker id="fk-arrow" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
            <polygon points="0,1 8,5 0,9" fill="#0284c7"/>
        </marker>
        <marker id="fk-arrow-amber" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
            <polygon points="0,1 8,5 0,9" fill="#d97706"/>
        </marker>
        <marker id="fk-arrow-purple" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
            <polygon points="0,1 8,5 0,9" fill="#7c3aed"/>
        </marker>
        <marker id="fk-arrow-green" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
            <polygon points="0,1 8,5 0,9" fill="#059669"/>
        </marker>
    </defs>

    <!-- Canvas Background -->
    <rect width="${W}" height="${H}" fill="#ffffff"/>

    <!-- Subtle Background Grid Pattern -->
    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#f1f5f9" stroke-width="1"/>
    </pattern>
    <rect width="${W}" height="${H}" fill="url(#grid)" opacity="0.8"/>

    <!-- Header Banner -->
    <g transform="translate(40, 25)">
        <rect x="0" y="0" width="${W - 80}" height="76" rx="12" fill="#0f172a"/>
        
        <!-- Header Text -->
        <text x="35" y="34" font-family="'Plus Jakarta Sans', -apple-system, sans-serif" font-size="19" font-weight="800" fill="#ffffff" letter-spacing="0.5">
            DATABASE ENTITY RELATIONSHIP DIAGRAM (ERD)
        </text>
        <text x="35" y="56" font-family="'Plus Jakarta Sans', -apple-system, sans-serif" font-size="12" font-weight="500" fill="#94a3b8">
            Academic Performance Monitoring System with Analytics (APMS) • DepEd High School Relational Schema &amp; Data Architecture
        </text>

        <!-- Right badges in header -->
        <g transform="translate(${W - 380}, 24)">
            <rect x="0" y="0" width="120" height="28" rx="6" fill="#1e293b" stroke="#334155"/>
            <text x="60" y="18" font-family="'Plus Jakarta Sans', sans-serif" font-size="11" font-weight="700" fill="#38bdf8" text-anchor="middle">18 ENTITIES</text>
            <rect x="130" y="0" width="140" height="28" rx="6" fill="#1e293b" stroke="#334155"/>
            <text x="200" y="18" font-family="'Plus Jakarta Sans', sans-serif" font-size="11" font-weight="700" fill="#34d399" text-anchor="middle">CROW'S FOOT NOTATION</text>
        </g>
    </g>

    <!-- Visual Domain Zone Backdrops -->
    <!-- 1. Identity & Profiles Domain -->
    <g opacity="0.6">
        <rect x="35" y="115" width="745" height="825" rx="16" fill="#f8fafc" stroke="#cbd5e1" stroke-width="1.5" stroke-dasharray="6,4"/>
        <rect x="45" y="125" width="230" height="24" rx="6" fill="#e2e8f0"/>
        <text x="55" y="141" font-family="'Plus Jakarta Sans', sans-serif" font-size="11" font-weight="800" fill="#334155">DOMAIN 1: USER &amp; DEMOGRAPHICS</text>
    </g>

    <!-- 2. Academic Calendar & Structure Domain -->
    <g opacity="0.6">
        <rect x="805" y="115" width="765" height="240" rx="16" fill="#f5f3ff" stroke="#c4b5fd" stroke-width="1.5" stroke-dasharray="6,4"/>
        <rect x="815" y="125" width="220" height="24" rx="6" fill="#ede9fe"/>
        <text x="825" y="141" font-family="'Plus Jakarta Sans', sans-serif" font-size="11" font-weight="800" fill="#5b21b6">DOMAIN 2: ACADEMIC CALENDAR</text>
    </g>

    <!-- 3. Offering, Curricula & Allocation Domain -->
    <g opacity="0.6">
        <rect x="805" y="370" width="765" height="570" rx="16" fill="#fefce8" stroke="#fde047" stroke-width="1.5" stroke-dasharray="6,4"/>
        <rect x="815" y="380" width="260" height="24" rx="6" fill="#fef08a"/>
        <text x="825" y="396" font-family="'Plus Jakarta Sans', sans-serif" font-size="11" font-weight="800" fill="#854d0e">DOMAIN 3: SECTIONS &amp; CURRICULUM</text>
    </g>

    <!-- 4. DepEd Evaluation, Honors & Audit Domain -->
    <g opacity="0.6">
        <rect x="35" y="950" width="1705" height="340" rx="16" fill="#f0fdf4" stroke="#86efac" stroke-width="1.5" stroke-dasharray="6,4"/>
        <rect x="45" y="960" width="280" height="24" rx="6" fill="#dcfce7"/>
        <text x="55" y="976" font-family="'Plus Jakarta Sans', sans-serif" font-size="11" font-weight="800" fill="#15803d">DOMAIN 4: GRADES, HONORS &amp; AUDIT</text>
    </g>
`;

    // Render Connector Paths (Foreign Keys & Cardinality)
    // We draw orthogonal connectors with clean bend corners
    const connectors = [
        // 1. users -> teachers (1:1)
        {
            path: 'M 370, 200 L 440, 200',
            stroke: '#0284c7', markerStart: 'url(#card-one)', markerEnd: 'url(#card-one)',
            label: '1 : 1 (user_id)', lx: 405, ly: 192
        },
        // 2. users -> students (1:1)
        {
            path: 'M 320, 400 L 320, 500 L 440, 500',
            stroke: '#0284c7', markerStart: 'url(#card-one)', markerEnd: 'url(#card-one)',
            label: '1 : 1 (user_id)', lx: 360, ly: 490
        },
        // 3. users -> parents (1:1)
        {
            path: 'M 210, 400 L 210, 460',
            stroke: '#0284c7', markerStart: 'url(#card-one)', markerEnd: 'url(#card-one)',
            label: '1 : 1 (user_id)', lx: 220, ly: 435
        },
        // 4. users -> activity_logs (1:N)
        {
            path: 'M 90, 400 L 90, 960',
            stroke: '#64748b', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (user_id)', lx: 100, ly: 680
        },
        // 5. parents -> parent_student (1:N)
        {
            path: 'M 160, 680 L 160, 730',
            stroke: '#0284c7', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (parent_id)', lx: 170, ly: 710
        },
        // 6. students -> parent_student (1:N)
        {
            path: 'M 440, 620 L 390, 620 L 390, 780 L 370, 780',
            stroke: '#0891b2', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (student_id)', lx: 400, ly: 660
        },
        // 7. students -> student_enrollments (1:N)
        {
            path: 'M 600, 700 L 600, 770',
            stroke: '#d97706', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (student_id)', lx: 610, ly: 735
        },
        // 8. teachers -> school_year_sections (1:N adviser)
        {
            path: 'M 760, 220 L 800, 220 L 800, 450 L 830, 450',
            stroke: '#0284c7', markerStart: 'url(#card-one)', markerEnd: 'url(#card-zero-many)',
            label: '1 : N (adviser_id)', lx: 790, ly: 340
        },
        // 9. teachers -> section_subjects (1:N teacher assignment)
        {
            path: 'M 760, 270 L 785, 270 L 785, 730 L 830, 730',
            stroke: '#0284c7', markerStart: 'url(#card-one)', markerEnd: 'url(#card-zero-many)',
            label: '1 : N (teacher_id)', lx: 780, ly: 580
        },
        // 10. school_years -> quarters (1:N)
        {
            path: 'M 1150, 200 L 1220, 200',
            stroke: '#7c3aed', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (school_year_id)', lx: 1185, ly: 190
        },
        // 11. school_years -> school_year_sections (1:N)
        {
            path: 'M 990, 330 L 990, 400',
            stroke: '#7c3aed', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (school_year_id)', lx: 1000, ly: 365
        },
        // 12. year_levels -> school_year_sections (1:N)
        {
            path: 'M 1220, 480 L 1150, 480',
            stroke: '#9333ea', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (year_level_id)', lx: 1180, ly: 470
        },
        // 13. sections -> school_year_sections (1:N)
        {
            path: 'M 1220, 650 L 1180, 650 L 1180, 540 L 1150, 540',
            stroke: '#9333ea', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (section_id)', lx: 1185, ly: 580
        },
        // 14. school_year_sections -> student_enrollments (1:N)
        {
            path: 'M 830, 530 L 780, 530 L 780, 830 L 760, 830',
            stroke: '#d97706', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (sy_section_id)', lx: 775, ly: 680
        },
        // 15. school_year_sections -> section_subjects (1:N)
        {
            path: 'M 990, 630 L 990, 700',
            stroke: '#d97706', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (sy_section_id)', lx: 1000, ly: 665
        },
        // 16. subjects -> section_subjects (1:N)
        {
            path: 'M 1220, 800 L 1150, 800',
            stroke: '#9333ea', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (subject_id)', lx: 1180, ly: 790
        },
        // 17. student_enrollments -> grades (1:N)
        {
            path: 'M 600, 980 L 600, 1070 L 650, 1070',
            stroke: '#059669', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (student_enrollment_id)', lx: 590, ly: 1040
        },
        // 18. section_subjects -> grades (1:N)
        {
            path: 'M 890, 900 L 890, 1010',
            stroke: '#059669', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (section_subject_id)', lx: 900, ly: 955
        },
        // 19. quarters -> grades (1:N)
        {
            path: 'M 1380, 360 L 1380, 940 L 980, 940 L 980, 1010',
            stroke: '#7c3aed', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (quarter_id)', lx: 1220, ly: 930
        },
        // 20. users -> grades (1:N created_by)
        {
            path: 'M 300, 400 L 300, 1140 L 650, 1140',
            stroke: '#2563eb', markerStart: 'url(#card-one)', markerEnd: 'url(#card-zero-many)',
            label: '1 : N (created_by)', lx: 460, ly: 1130
        },
        // 21. school_years -> achievement_rules (1:N)
        {
            path: 'M 1140, 330 L 1140, 980',
            stroke: '#db2777', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (school_year_id)', lx: 1145, ly: 640
        },
        // 22. students -> achievements (1:N)
        {
            path: 'M 760, 570 L 810, 570 L 810, 920 L 1550, 920 L 1550, 980',
            stroke: '#db2777', markerStart: 'url(#card-one)', markerEnd: 'url(#card-many)',
            label: '1 : N (student_id)', lx: 1470, ly: 910
        },
        // 23. school_years -> achievements (1:N)
        {
            path: 'M 1120, 330 L 1120, 370 L 1600, 370 L 1600, 980',
            stroke: '#db2777', markerStart: 'url(#card-one)', markerEnd: 'url(#card-zero-many)',
            label: '1 : N (school_year_id)', lx: 1530, ly: 360
        }
    ];

    // Render lines first so table cards sit on top
    for (const c of connectors) {
        svg += `
    <g class="connector-group">
        <path d="${c.path}" fill="none" stroke="${c.stroke}" stroke-width="2.2" 
              stroke-linecap="round" stroke-linejoin="round"
              marker-start="${c.markerStart}" marker-end="${c.markerEnd}"/>
        <rect x="${c.lx - 5}" y="${c.ly - 9}" width="${c.label.length * 6.5 + 10}" height="16" rx="4" fill="#ffffff" stroke="${c.stroke}" stroke-width="0.8" opacity="0.95"/>
        <text x="${c.lx}" y="${c.ly + 3}" font-family="'JetBrains Mono', 'Segoe UI', monospace" font-size="8.5" font-weight="700" fill="${c.stroke}">
            ${c.label}
        </text>
    </g>`;
    }

    // Render Table Entities
    for (const [key, t] of Object.entries(tables)) {
        svg += `
    <!-- TABLE ENTITY: ${t.name} -->
    <g id="entity-${t.name}" transform="translate(${t.x}, ${t.y})" filter="url(#erd-shadow)">
        <!-- Table Frame Base -->
        <rect x="0" y="0" width="${t.w}" height="${t.h}" rx="8" fill="#ffffff" stroke="#cbd5e1" stroke-width="1.4"/>

        <!-- Header Ribbon -->
        <rect x="0" y="0" width="${t.w}" height="${HEADER_H}" rx="8" fill="${t.headerBg}"/>
        <rect x="0" y="${HEADER_H - 8}" width="${t.w}" height="8" fill="${t.headerBg}"/>

        <!-- Stereotype -->
        <text x="14" y="16" font-family="'Plus Jakarta Sans', sans-serif" font-size="9" font-weight="700" fill="#e0f2fe" opacity="0.9">
            ${t.stereotype}
        </text>

        <!-- Table Name -->
        <text x="14" y="34" font-family="'Plus Jakarta Sans', sans-serif" font-size="14" font-weight="800" fill="#ffffff">
            ${t.title}
        </text>

        <!-- Header Count Badge -->
        <rect x="${t.w - 55}" y="12" width="42" height="20" rx="4" fill="rgba(0,0,0,0.22)"/>
        <text x="${t.w - 34}" y="26" font-family="'JetBrains Mono', monospace" font-size="9" font-weight="700" fill="#ffffff" text-anchor="middle">
            ${t.columns.length} col
        </text>

        <!-- Column Rows -->
        <g transform="translate(0, ${HEADER_H})">`;

        t.columns.forEach((col, idx) => {
            const rowY = idx * ROW_H;
            const isAlt = idx % 2 === 1;
            const bgFill = isAlt ? '#f8fafc' : '#ffffff';

            svg += `
            <!-- Row ${idx + 1}: ${col.name} -->
            <rect x="1" y="${rowY}" width="${t.w - 2}" height="${ROW_H}" fill="${bgFill}"/>
            <line x1="1" y1="${rowY + ROW_H}" x2="${t.w - 1}" y2="${rowY + ROW_H}" stroke="#f1f5f9" stroke-width="1"/>`;

            // Key badges
            if (col.key.includes('PK')) {
                svg += `
            <rect x="10" y="${rowY + 3}" width="22" height="15" rx="3" fill="#eab308"/>
            <text x="21" y="${rowY + 14}" font-family="'Plus Jakarta Sans', sans-serif" font-size="8.5" font-weight="800" fill="#713f12" text-anchor="middle">PK</text>`;
            } else if (col.key.includes('FK')) {
                svg += `
            <rect x="10" y="${rowY + 3}" width="22" height="15" rx="3" fill="#3b82f6"/>
            <text x="21" y="${rowY + 14}" font-family="'Plus Jakarta Sans', sans-serif" font-size="8.5" font-weight="800" fill="#ffffff" text-anchor="middle">FK</text>`;
            } else if (col.key.includes('UK')) {
                svg += `
            <rect x="10" y="${rowY + 3}" width="22" height="15" rx="3" fill="#8b5cf6"/>
            <text x="21" y="${rowY + 14}" font-family="'Plus Jakarta Sans', sans-serif" font-size="8.5" font-weight="800" fill="#ffffff" text-anchor="middle">UK</text>`;
            }

            // Second key badge if composite like FK,UK
            if (col.key === 'FK,UK') {
                svg += `
            <rect x="34" y="${rowY + 3}" width="22" height="15" rx="3" fill="#8b5cf6"/>
            <text x="45" y="${rowY + 14}" font-family="'Plus Jakarta Sans', sans-serif" font-size="8.5" font-weight="800" fill="#ffffff" text-anchor="middle">UK</text>`;
            }

            const nameX = col.key === 'FK,UK' ? 62 : (col.key ? 38 : 16);

            // Column Name
            const isBold = col.key ? '800' : '600';
            const nameColor = col.key ? '#0f172a' : '#334155';
            svg += `
            <text x="${nameX}" y="${rowY + 14}" font-family="'JetBrains Mono', 'Segoe UI', monospace" font-size="9" font-weight="${isBold}" fill="${nameColor}">
                ${col.name}
            </text>`;

            // Column Type (Right aligned or right offset)
            const typeColor = col.null ? '#64748b' : '#0369a1';
            svg += `
            <text x="${t.w - 12}" y="${rowY + 14}" font-family="'JetBrains Mono', 'Segoe UI', monospace" font-size="8.5" font-weight="500" fill="${typeColor}" text-anchor="end">
                ${col.type}${col.null ? ' [N]' : ''}
            </text>`;
        });

        svg += `
        </g>
    </g>`;
    }

    // Legend & Architecture Summary Cards at bottom
    svg += `
    <!-- ========================================================
         BOTTOM LEGEND & SPECIFICATION CARDS
         ======================================================== -->
    <g transform="translate(40, 1310)">
        <!-- Card 1: Notation Legend -->
        <g transform="translate(0, 0)">
            <rect x="0" y="0" width="460" height="145" rx="10" fill="#ffffff" stroke="#cbd5e1" stroke-width="1.2" filter="url(#erd-shadow)"/>
            <rect x="0" y="0" width="460" height="28" rx="10" fill="#f1f5f9"/>
            <rect x="0" y="20" width="460" height="8" fill="#f1f5f9"/>
            <text x="14" y="19" font-family="'Plus Jakarta Sans', sans-serif" font-size="11" font-weight="800" fill="#1e293b">ERD NOTATION &amp; CARDINALITY GUIDE</text>

            <g transform="translate(14, 40)">
                <!-- 1:1 -->
                <line x1="0" y1="12" x2="35" y2="12" stroke="#475569" stroke-width="2"/>
                <line x1="12" y1="5" x2="12" y2="19" stroke="#475569" stroke-width="2"/>
                <line x1="18" y1="5" x2="18" y2="19" stroke="#475569" stroke-width="2"/>
                <text x="45" y="15" font-family="'Plus Jakarta Sans', sans-serif" font-size="10" font-weight="700" fill="#334155">Exactly One (1 : 1)</text>

                <!-- 1:N -->
                <g transform="translate(230, 0)">
                    <line x1="0" y1="12" x2="35" y2="12" stroke="#475569" stroke-width="2"/>
                    <line x1="10" y1="5" x2="10" y2="19" stroke="#475569" stroke-width="2"/>
                    <path d="M 28 5 L 18 12 L 28 19" fill="none" stroke="#475569" stroke-width="2"/>
                    <text x="45" y="15" font-family="'Plus Jakarta Sans', sans-serif" font-size="10" font-weight="700" fill="#334155">One or Many (1 : N)</text>
                </g>

                <!-- 0:N -->
                <g transform="translate(0, 32)">
                    <line x1="0" y1="12" x2="35" y2="12" stroke="#475569" stroke-width="2"/>
                    <circle cx="10" cy="12" r="4.5" fill="#ffffff" stroke="#475569" stroke-width="1.8"/>
                    <path d="M 32 5 L 20 12 L 32 19" fill="none" stroke="#475569" stroke-width="2"/>
                    <text x="45" y="15" font-family="'Plus Jakarta Sans', sans-serif" font-size="10" font-weight="700" fill="#334155">Zero or Many (0 : N)</text>
                </g>

                <!-- Key Badges -->
                <g transform="translate(230, 32)">
                    <rect x="0" y="3" width="22" height="15" rx="3" fill="#eab308"/>
                    <text x="11" y="14" font-family="'Plus Jakarta Sans', sans-serif" font-size="8.5" font-weight="800" fill="#713f12" text-anchor="middle">PK</text>
                    <text x="28" y="15" font-family="'Plus Jakarta Sans', sans-serif" font-size="9.5" fill="#475569">Primary Key</text>

                    <rect x="110" y="3" width="22" height="15" rx="3" fill="#3b82f6"/>
                    <text x="121" y="14" font-family="'Plus Jakarta Sans', sans-serif" font-size="8.5" font-weight="800" fill="#ffffff" text-anchor="middle">FK</text>
                    <text x="138" y="15" font-family="'Plus Jakarta Sans', sans-serif" font-size="9.5" fill="#475569">Foreign Key</text>

                    <rect x="195" y="3" width="22" height="15" rx="3" fill="#8b5cf6"/>
                    <text x="206" y="14" font-family="'Plus Jakarta Sans', sans-serif" font-size="8.5" font-weight="800" fill="#ffffff" text-anchor="middle">UK</text>
                    <text x="223" y="15" font-family="'Plus Jakarta Sans', sans-serif" font-size="9.5" fill="#475569">Unique</text>
                </g>

                <!-- Status & Integrity rules -->
                <text x="0" y="68" font-family="'Plus Jakarta Sans', sans-serif" font-size="9" font-weight="600" fill="#64748b">
                    • Referential Actions: cascadeOnDelete (Profiles, Pivot) | restrictOnDelete (Grades, Calendar, Structure)
                </text>
                <text x="0" y="82" font-family="'Plus Jakarta Sans', sans-serif" font-size="9" font-weight="600" fill="#64748b">
                    • Soft Deletions: Governed by status enum transitions ('active'/'inactive'/'draft'/'locked')
                </text>
            </g>
        </g>

        <!-- Card 2: Relational Architecture Highlights -->
        <g transform="translate(480, 0)">
            <rect x="0" y="0" width="600" height="145" rx="10" fill="#ffffff" stroke="#cbd5e1" stroke-width="1.2" filter="url(#erd-shadow)"/>
            <rect x="0" y="0" width="600" height="28" rx="10" fill="#f1f5f9"/>
            <rect x="0" y="20" width="600" height="8" fill="#f1f5f9"/>
            <text x="14" y="19" font-family="'Plus Jakarta Sans', sans-serif" font-size="11" font-weight="800" fill="#1e293b">RELATIONAL ARCHITECTURE &amp; DEPED COMPLIANCE HIGHLIGHTS</text>

            <g transform="translate(14, 42)">
                <text x="0" y="10" font-family="'Plus Jakarta Sans', sans-serif" font-size="9.5" font-weight="700" fill="#0f172a">
                    1. Atomic Fact Table: <tspan font-weight="500" fill="#475569">The <tspan font-family="'JetBrains Mono', monospace" font-weight="700">grades</tspan> table stores quarterly grades with a unique constraint on (student_enrollment_id, section_subject_id, quarter_id).</tspan>
                </text>
                <text x="0" y="28" font-family="'Plus Jakarta Sans', sans-serif" font-size="9.5" font-weight="700" fill="#0f172a">
                    2. Role-Segregated Profiles: <tspan font-weight="500" fill="#475569">The <tspan font-family="'JetBrains Mono', monospace" font-weight="700">users</tspan> table handles authentication while teachers, students, and parents hold domain-specific profile records.</tspan>
                </text>
                <text x="0" y="46" font-family="'Plus Jakarta Sans', sans-serif" font-size="9.5" font-weight="700" fill="#0f172a">
                    3. Reusable Catalogs vs Instance Offerings: <tspan font-weight="500" fill="#475569"><tspan font-family="'JetBrains Mono', monospace" font-weight="700">sections</tspan> is reusable; <tspan font-family="'JetBrains Mono', monospace" font-weight="700">school_year_sections</tspan> instantiates sections per academic year.</tspan>
                </text>
                <text x="0" y="64" font-family="'Plus Jakarta Sans', sans-serif" font-size="9.5" font-weight="700" fill="#0f172a">
                    4. Honors Policy Engine: <tspan font-weight="500" fill="#475569"><tspan font-family="'JetBrains Mono', monospace" font-weight="700">achievement_rules</tspan> defines thresholds (DepEd Order 36, s. 2016) evaluating grades into <tspan font-family="'JetBrains Mono', monospace" font-weight="700">achievements</tspan>.</tspan>
                </text>
                <text x="0" y="82" font-family="'Plus Jakarta Sans', sans-serif" font-size="9.5" font-weight="700" fill="#0f172a">
                    5. Non-Repudiation Audit: <tspan font-weight="500" fill="#475569"><tspan font-family="'JetBrains Mono', monospace" font-weight="700">activity_logs</tspan> captures all polymorphic modifications with old and new JSON payloads for accountability.</tspan>
                </text>
            </g>
        </g>
    </g>

    <!-- Footer Copyright Watermark -->
    <text x="${W / 2}" y="${H - 18}" font-family="'Plus Jakarta Sans', sans-serif" font-size="10.5" font-weight="600" fill="#94a3b8" text-anchor="middle">
        Academic Performance Monitoring System with Analytics • Entity Relationship Diagram (ERD) • Database Design Specification
    </text>
</svg>`;

    return svg;
}

const outputPath = path.resolve('public/flowcharts/entity_relationship_diagram.svg');
fs.writeFileSync(outputPath, renderSVG(), 'utf8');
console.log('Successfully generated ' + outputPath);
