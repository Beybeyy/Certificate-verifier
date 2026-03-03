<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us | CerVer - Certificate Verifier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/cerverlogo2.svg">

    <style>
        /* Unified Global Styles from About Page */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #e4e4e6;
            color: #1a1a1a;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== TOP NAV (Exact Match) ===== */
        .top-nav {
            background-color: #0b4a82;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #ffffff;
            position: relative;
            z-index: 1000;
        }

        .nav-brand {
            font-size: 20px;
            font-weight: bold;
            line-height: 1.2;
        }

        .nav-brand strong {
            font-size: 22px;
            font-weight: 300;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: #ffffff;
            text-decoration: none;
            font-size: 15px;
            transition: 0.3s;
        }

        .nav-links a:hover { opacity: 0.8; text-decoration: underline; }

        .burger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
            z-index: 1001;
        }

        .burger span {
            height: 3px;
            width: 25px;
            background: white;
            border-radius: 3px;
            transition: 0.4s;
        }

        /* ===== MAIN CONTENT ===== */
        .container {
            max-width: 1120px;
            width: 100%;
            margin: 40px auto;
            padding: 0 20px;
            flex: 1;
        }

        h2 {
            font-size: 32px;
            color: #0b4a82;
            margin-bottom: 20px;
            border-bottom: 2px solid #0b4a82;
            display: inline-block;
            padding-bottom: 5px;
        }

        .content-box {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            border: 1px solid #0b4a82;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        /* Contact Header Layout */
        .contact-header { 
            display: flex; 
            justify-content: space-between; 
            gap: 40px; 
            margin-bottom: 30px;
        }
        
        .contact-text { flex: 1; line-height: 1.8; }
        .contact-text p { margin-bottom: 15px; }

        .map-box { 
            flex: 1; 
            height: 250px; 
            border: 1px solid #ddd; 
            border-radius: 10px; 
            overflow: hidden; 
        }

        /* Table Tabs */
        .tabs { 
            display: flex; 
            gap: 5px; 
            margin-bottom: -1px; 
            overflow-x: auto;
        }
        
        .tab { 
            padding: 12px 20px; 
            background: #eee; 
            border: 1px solid #0b4a82; 
            cursor: pointer; 
            font-size: 13px; 
            font-weight: bold; 
            border-radius: 10px 10px 0 0;
            transition: 0.3s;
            white-space: nowrap;
        }
        
        .tab.active { background: #0b4a82; color: white; }

        /* Table Styling */
        .table-container { background: #fff; border: 1px solid #0b4a82; border-radius: 0 10px 10px 10px; overflow: hidden; }
        
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #0b4a82; color: white; padding: 15px; text-align: left; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 13px; vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .hidden { display: none; }
        .office-tag { display: block; font-weight: bold; color: #0b4a82; margin-bottom: 4px; font-size: 11px; text-transform: uppercase; }

        /* ===== FOOTER ===== */
        footer { 
            background-color: #fff; 
            padding: 20px 40px; 
            font-size: 13px; 
            border-top: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: 40px;
        }

        /* ===== MOBILE RESPONSIVE ===== */
        @media (max-width: 992px) {
            .contact-header { flex-direction: column; }
            .map-box { width: 100%; }
        }

        @media (max-width: 768px) {
            .top-nav { padding: 15px 20px; }
            .burger { display: flex; }
            .nav-links {
                position: fixed;
                right: -100%;
                top: 0;
                height: 100vh;
                width: 200px;
                background: #0b4a82;
                flex-direction: column;
                padding: 80px 20px;
                transition: 0.4s ease-in-out;
                z-index: 999;
            }
            .nav-links.active { right: 0; }
            .nav-links a { font-size: 18px; width: 100%; padding: 15px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
            
            .burger.toggle span:nth-child(1) { transform: rotate(-45deg) translate(-5px, 6px); }
            .burger.toggle span:nth-child(2) { opacity: 0; }
            .burger.toggle span:nth-child(3) { transform: rotate(45deg) translate(-5px, -6px); }

            footer { flex-direction: column; text-align: center; gap: 10px; }
        }
    </style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">
        DEPARTMENT OF EDUCATION<br>
        <strong>CerVer - Certificate Verifier</strong>
    </div>

    <div class="burger" id="burger">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="nav-links" id="nav-menu">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </div>
</nav>

<div class="container">
    <h2>Contact Us</h2> 

    <div class="content-box">
        <div class="contact-header">
            <div class="contact-text">
                <p><strong>EMAIL</strong><br>
                sanjosedelmonte.city@deped.gov.ph</p>
                <p><strong>ADDRESS</strong><br>
                Eco Park, Muzon East, City of San Jose del Monte, Bulacan</p>
            </div>
            <div class="map-box">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1928.664657389031!2d121.03986799025172!3d14.806764456792889!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397af08bb88220b%3A0x11e6f66be46ec8e!2sDepartment%20of%20Education%20-%20Divisional%20Offices%20(San%20Jose%20Del%20Monte)!5e0!3m2!1sen!2sph!4v1772508572916!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>

    <div class="tabs">
        <div class="tab active" onclick="switchTab(0)">OFFICE OF THE SCHOOLS DIVISION SUPERINTENDENT</div>
        <div class="tab" onclick="switchTab(1)">CURRICULUM IMPLEMENTATION DIVISION</div>
        <div class="tab" onclick="switchTab(2)">SCHOOL GOVERNANCE & OPERATIONS DIVISION</div>
    </div>

    <div class="table-container">
        <table id="table-0">
            <thead><tr><th>OFFICE / SECTION</th><th>HEAD OF OFFICE</th><th>CONTACT DETAILS</th></tr></thead>
            <tbody>
            <tr>
                <td><strong>Office of the Schools Division Superintendent</strong></td>
                <td>Leonardo C. Canlas, EdD CESO VI<br><small>Assistant Schools Division Superintendent, OIC</small></td>
                <td>Email: sanjosedelmonte.city@deped.gov.ph<br>Tel: (044) 305-7395 loc. 201, 203</td>
            </tr>
            <tr>
                <td><strong>Personnel Section</strong></td>
                <td>Juanaly O. Lacal<br><small>Administrative Officer IV</small></td>
                <td>Email: juanaly.lacal@deped.gov.ph<br>Tel: (044) 305-7395 loc. 106</td>
            </tr>
            <tr>
                <td><strong>Records Section</strong></td>
                <td>Dennis P. Garcia<br><small>Administrative Officer IV</small></td>
                <td>Email: dennis.garcia@deped.gov.ph<br>Tel: (044) 305-7395 loc. 108</td>
            </tr>
            <tr>
                <td><strong>Cash Section</strong></td>
                <td>Jeanny G. Roldan<br><small>Administrative Officer IV</small></td>
                <td>Email: jeanny.roldan001@deped.gov.ph<br>Tel: (044) 305-7395 loc. 105</td>
            </tr>
            <tr>
                <td><strong>Property and Supply Section</strong></td>
                <td>Ma. Theresa M. Roxas<br><small>Administrative Officer IV</small></td>
                <td>Email: matheresa.roxas@deped.gov.ph<br>Tel: (044) 305-7395 loc. 110, 112</td>
            </tr>
            <tr>
                <td><strong>Accounting</strong></td>
                <td>Kristine Joy D. Quezada<br><small>Accountant III</small></td>
                <td>Email: kristinejoy.quezada@deped.gov.ph<br>Tel: (044) 305-7395 loc. 103</td>
            </tr>
            <tr>
                <td><strong>Budget</strong></td>
                <td>Orlando D. Gonzales<br><small>Administrative Officer V</small></td>
                <td>Email: orlando.gonzales002@deped.gov.ph<br>Tel: (044) 305-7395 loc. 102</td>
            </tr>
            <tr>
                <td><strong>Legal Services</strong></td>
                <td>Atty. Ira Kim C. Victoria<br><small>Attorney III</small></td>
                <td>Email: irakim.victoria@deped.gov.ph<br>Tel: (044) 305-7395 loc. 205</td>
            </tr>
            <tr>
                <td><strong>ICT Unit</strong></td>
                <td>Arthur F. Francisco<br><small>Information Technology Officer I</small></td>
                <td>Email: arthur.francisco@deped.gov.ph<br>Tel: (044) 305-7395 loc. 111</td>
            </tr>
            <tr>
                <td><strong>Payroll Section</strong></td>
                <td>Baby Ruth D. Pablo<br><small>Administrative Officer II</small></td>
                <td>Email: babyruth.pablo@deped.gov.ph<br>Tel: (044) 305-7395 loc. 107</td>
            </tr>
            </tbody>
    </table>

        <table id="table-1" class="hidden">
            <thead><tr><th>OFFICE / AREA</th><th>HEAD OF OFFICE</th><th>CONTACT INFO</th></tr></thead>
            <tbody>
            <tr>
                <th>OFFICE / AREA</th>
                <th>HEAD OF OFFICE</th>
                <th>CONTACT INFORMATION</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="office-tag">CID Office</span>Office of the Curriculum and Implementation Division</td>
                <td><strong>Rolando T. Sotelo DEM</strong><br>Chief Education Supervisor</td>
                <td>Email: rolando.sotelo001@deped.gov.ph<br>Tel: (044) 305-7395 loc. 206, 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>EPP/TLE/TVL</td>
                <td><strong>Ruby M. Cagadas EdD</strong><br>Education Program Supervisor</td>
                <td>Email: ruby.cagadas001@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>Araling Panlipunan</td>
                <td><strong>Emmanuel V. De Mesa</strong><br>Education Program Supervisor</td>
                <td>Email: emmanuel.demesa001@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>MAPEH</td>
                <td><strong>Leny B. Delos Reyes</strong><br>Education Program Supervisor</td>
                <td>Email: leny.delosreyes001@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>Science</td>
                <td><strong>Esperanza D. Española</strong><br>Education Program Supervisor</td>
                <td>Email: esperanza.espanola001@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>Mathematics</td>
                <td><strong>Ma. Corazon P. Loja</strong><br>Education Program Supervisor</td>
                <td>Email: macorazon.loja@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>Edukasyon sa Pagpapakatao</td>
                <td><strong>Maria Cristina H. Nogoy PhD</strong><br>Education Program Supervisor</td>
                <td>Email: mariacristina.nogoy@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>English</td>
                <td><strong>Marlon P. Daclis</strong><br>Education Program Supervisor</td>
                <td>Email: marlon.daclis001@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>Filipino</td>
                <td><strong>Elizabeth B. Eligio</strong><br>Education Program Supervisor</td>
                <td>Email: elizabeth.eligio001@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Alternative Learning System</span>ALS</td>
                <td><strong>Senen B. Jane</strong><br>Education Program Supervisor</td>
                <td>Email: senen.jane@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">District Supervision</span>Public Schools District Supervisor</td>
                <td><strong>Arlon P. Cadiz</strong> / <strong>Darlan R. Grageda Jr.</strong></td>
                <td>arlon.cadiz@deped.gov.ph<br>darlan.grageda001@deped.gov.ph</td>
            </tr>
            <tr>
                <td><span class="office-tag">District Supervision</span>Public Schools District Supervisor</td>
                <td><strong>Lourdes R. Robes PhD</strong> / <strong>Ma. Socorro B. Lindo</strong></td>
                <td>lourdes.robes@deped.gov.ph<br>masocorro.lindo@deped.gov.ph</td>
            </tr>
            <tr>
                <td><span class="office-tag">Learning Resource Management</span>LRM</td>
                <td><strong>Annalyn L. German EdD</strong><br>Education Program Supervisor</td>
                <td>Email: annalyn.german@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
        </tbody>
    </table>

        <table id="table-2" class="hidden">
            <thead><tr><th>OFFICE</th><th>HEAD OF OFFICE</th><th>CONTACT</th></tr></thead>
            <tbody>
            <tr>
                <td><span class="office-tag">CID Office</span>Office of the Curriculum and Implementation Division</td>
                <td><strong>Rolando T. Sotelo DEM</strong><br>Chief Education Supervisor</td>
                <td>Email: rolando.sotelo001@deped.gov.ph<br>Tel: (044) 305-7395 loc. 206, 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>EPP/TLE/TVL</td>
                <td><strong>Ruby M. Cagadas EdD</strong><br>Education Program Supervisor</td>
                <td>Email: ruby.cagadas001@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>Araling Panlipunan</td>
                <td><strong>Emmanuel V. De Mesa</strong><br>Education Program Supervisor</td>
                <td>Email: emmanuel.demesa001@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>MAPEH</td>
                <td><strong>Leny B. Delos Reyes</strong><br>Education Program Supervisor</td>
                <td>Email: leny.delosreyes001@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>Science</td>
                <td><strong>Esperanza D. Española</strong><br>Education Program Supervisor</td>
                <td>Email: esperanza.espanola001@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>Mathematics</td>
                <td><strong>Ma. Corazon P. Loja</strong><br>Education Program Supervisor</td>
                <td>Email: macorazon.loja@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>Edukasyon sa Pagpapakatao</td>
                <td><strong>Maria Cristina H. Nogoy PhD</strong><br>Education Program Supervisor</td>
                <td>Email: mariacristina.nogoy@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>English</td>
                <td><strong>Marlon P. Daclis</strong><br>Education Program Supervisor</td>
                <td>Email: marlon.daclis001@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Instructional Management</span>Filipino</td>
                <td><strong>Elizabeth B. Eligio</strong><br>Education Program Supervisor</td>
                <td>Email: elizabeth.eligio001@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">Alternative Learning System</span>ALS</td>
                <td><strong>Senen B. Jane</strong><br>Education Program Supervisor</td>
                <td>Email: senen.jane@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
            <tr>
                <td><span class="office-tag">District Supervision</span>Public Schools District Supervisor</td>
                <td><strong>Arlon P. Cadiz</strong> / <strong>Darlan R. Grageda Jr.</strong></td>
                <td>arlon.cadiz@deped.gov.ph<br>darlan.grageda001@deped.gov.ph</td>
            </tr>
            <tr>
                <td><span class="office-tag">District Supervision</span>Public Schools District Supervisor</td>
                <td><strong>Lourdes R. Robes PhD</strong> / <strong>Ma. Socorro B. Lindo</strong></td>
                <td>lourdes.robes@deped.gov.ph<br>masocorro.lindo@deped.gov.ph</td>
            </tr>
            <tr>
                <td><span class="office-tag">Learning Resource Management</span>LRM</td>
                <td><strong>Annalyn L. German EdD</strong><br>Education Program Supervisor</td>
                <td>Email: annalyn.german@deped.gov.ph<br>Tel: loc. 207</td>
            </tr>
        </tr>
    </tbody>
    </table>
    </div>
</div>

<footer>
    <div class="footer-left">
        © 2026 Department of Education Certificate Verifier System
    </div>
    <div class="footer-right">
        Developed by: Larry Cruz and Bea Patrice Cortez
    </div>
</footer>

<script>
    // Navigation Logic
    const burger = document.getElementById('burger');
    const navMenu = document.getElementById('nav-menu');

    burger.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        burger.classList.toggle('toggle');
    });

    // Tab Switching Logic
    function switchTab(index) {
        const tabs = document.querySelectorAll('.tab');
        const tables = [
            document.getElementById('table-0'),
            document.getElementById('table-1'),
            document.getElementById('table-2')
        ];

        tabs.forEach((tab, i) => {
            tab.classList.toggle('active', i === index);
        });

        tables.forEach((table, i) => {
            if (table) {
                table.classList.toggle('hidden', i !== index);
            }
        });
    }
</script>

</body>
</html>