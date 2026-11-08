const fs = require('fs');
const path = require('path');

const htmlPath = path.resolve('public/flowcharts.html');
const svgPath = path.resolve('public/flowcharts/entity_relationship_diagram.svg');

let html = fs.readFileSync(htmlPath, 'utf8');
let svgContent = fs.readFileSync(svgPath, 'utf8');

// Strip XML declaration if present
svgContent = svgContent.replace(/<\?xml[\s\S]*?\?>\s*/i, '');

// Ensure svg has id="svg-erd" and class="flowchart-svg"
svgContent = svgContent.replace(/<svg\s+([^>]*?)>/i, (match, attrs) => {
    let cleanAttrs = attrs.replace(/\bid="[^"]*"/i, '').replace(/\bclass="[^"]*"/i, '');
    return `<svg id="svg-erd" class="flowchart-svg" ${cleanAttrs}>`;
});

// 1. Add tab button
const targetTab = `<button class="tab-btn" onclick="switchTab('architecture')">
            <span>6. System Architecture</span>
            <span class="role-badge" style="background: rgba(16, 185, 129, 0.35); color: #a7f3d0;">3-Tier Engine</span>
        </button>`;

const newTab = `<button class="tab-btn" onclick="switchTab('architecture')">
            <span>6. System Architecture</span>
            <span class="role-badge" style="background: rgba(16, 185, 129, 0.35); color: #a7f3d0;">3-Tier Engine</span>
        </button>
        <button class="tab-btn" onclick="switchTab('erd')">
            <span>7. Entity Relationship Diagram (ERD)</span>
            <span class="role-badge" style="background: rgba(14, 165, 233, 0.35); color: #7dd3fc;">Data Model / Schema</span>
        </button>`;

if (!html.includes("switchTab('erd')")) {
    html = html.replace(targetTab, newTab);
}

// 2. Build Panel ERD
const panelErd = `
    <!-- ==========================================
         PANEL 7: ENTITY RELATIONSHIP DIAGRAM (ERD)
         ========================================== -->
    <div id="panel-erd" class="flowchart-panel">
        <div class="panel-header">
            <div class="panel-title-wrap">
                <h2>7. Entity Relationship Diagram (ERD) &amp; Relational Schema</h2>
                <p>Comprehensive 18-entity database model detailing Primary/Foreign Keys, Unique Constraints, Cardinalities, and DepEd High School business logic.</p>
            </div>
            <div class="actions-bar">
                <button class="btn-action btn-download-svg" onclick="downloadSVG('svg-erd', 'APMS_Entity_Relationship_Diagram.svg')">📥 Download SVG</button>
                <button class="btn-action btn-download-png" onclick="downloadPNG('svg-erd', 'APMS_Entity_Relationship_Diagram.png')">🖼️ Download PNG</button>
                <button class="btn-action btn-print" onclick="window.print()">🖨️ Print / PDF</button>
            </div>
        </div>

        <div class="flowchart-viewport">
            <!-- PURE SVG ERD -->
            ${svgContent}
        </div>

        <div class="panel-narrative">
            <div class="narrative-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                Relational Architecture, Data Integrity &amp; DepEd Specification Highlights
            </div>
            <div class="narrative-list">
                <div class="narrative-item">
                    <strong>1. Core Fact Table (grades)</strong>
                    Serves as the central transaction record for quarterly student assessments. Enforces strict uniqueness on <code style="color:#93c5fd;">(student_enrollment_id, section_subject_id, quarter_id)</code>, preventing duplicate submissions while storing computed quarterly marks, transmutation remarks, lock statuses, and author auditing.
                </div>
                <div class="narrative-item">
                    <strong>2. Role-Segregated User Profiles</strong>
                    The central <code style="color:#93c5fd;">users</code> table encapsulates authentication credentials and RBAC roles ('admin', 'teacher', 'student', 'parent'). Specific academic and demographic attributes reside in dedicated profile entities (<code style="color:#93c5fd;">teachers</code>, <code style="color:#93c5fd;">students</code>, <code style="color:#93c5fd;">parents</code>) with 1:1 foreign key relationships and cascade deletions.
                </div>
                <div class="narrative-item">
                    <strong>3. Reusable Catalogs vs. Operational Instances</strong>
                    Curriculum standards remain decoupled from annual rotations: <code style="color:#93c5fd;">sections</code> and <code style="color:#93c5fd;">year_levels</code> are reusable master templates, while <code style="color:#93c5fd;">school_year_sections</code> instantiates active classroom units per school year with specific section advisers.
                </div>
                <div class="narrative-item">
                    <strong>4. Class Scheduling &amp; Advisory Matrix</strong>
                    Section loads are partitioned via <code style="color:#93c5fd;">section_subjects</code>, connecting classroom instances to subjects and assigned faculty teachers. Advisers manage the general roster and SF9 generation, whereas subject teachers encode subject grades independently.
                </div>
                <div class="narrative-item">
                    <strong>5. DepEd Honors Engine (DepEd Order 36, s. 2016)</strong>
                    <code style="color:#93c5fd;">achievement_rules</code> models institutional honor thresholds (e.g., Minimum General Average &ge; 90.00, no failing grade in any learning area). The honors processor evaluates verified quarterly/final grades to persist awarded honors in <code style="color:#93c5fd;">achievements</code>.
                </div>
                <div class="narrative-item">
                    <strong>6. Comprehensive Audit Trail &amp; Non-Repudiation</strong>
                    The <code style="color:#93c5fd;">activity_logs</code> table captures all security, grading, and configuration events. Stores polymorphic entity references (<code style="color:#93c5fd;">subject_type</code>, <code style="color:#93c5fd;">subject_id</code>) alongside JSON diff snapshots (<code style="color:#93c5fd;">old_values</code>, <code style="color:#93c5fd;">new_values</code>) for complete legal and administrative accountability.
                </div>
            </div>
        </div>
    </div>
`;

// Insert panelErd right before <footer>
const footerTag = '    <footer>';
if (!html.includes('id="panel-erd"')) {
    html = html.replace(footerTag, panelErd + '\n' + footerTag);
}

fs.writeFileSync(htmlPath, html, 'utf8');
console.log('Successfully updated ' + htmlPath + ' with ERD panel and tab!');
