<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DepEd Certificate Verifier - Contact Us</title>
    <style>
         * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Arial, sans-serif;;
        }

        body {
            background-color: #ffffff;
            color: #000;
            
        }

          /* NAVBAR */
          .top-nav {
            background-color: #0b3c78;
            padding: 15px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            align-items: center;
            color: white;
            position: relative;
            z-index: 1000;
        }

        /* Left Brand Side */
        .nav-brand {
            text-align: left;
            line-height: 1.2;
            margin-left: 20px;
            font-weight: bold;
            font-size: 18px;
        }

        .nav-brand small {
            font-weight: normal;
            font-size: 14px;
            opacity: 0.9;
        }

        .nav-links {
            display: flex;
            align-items: center;
            transition: 0.3s ease-in-out;
        }

        .top-nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 20px;
            font-size: 16px;
            font-weight: 400;
        }

        .top-nav a:hover {
            text-decoration: underline;
        }
        
        .login-btn {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            display: block;
            margin-bottom: 20px;
        }
       
        .container { width: 95%; max-width: 1200px; margin: 40px auto; }

        /* Contact Info Section */
        .contact-header { display: flex; justify-content: space-between; margin-bottom: 30px; gap: 20px; }
        .contact-text h2 { font-size: 2rem; margin-top: 0; color: #004080; }
        .map-box { width: 400px; height: 200px; border: 1px solid #ccc; background: #f9f9f9; flex-shrink: 0; }

        /* Table Tabs */
        .tabs { display: flex; border-bottom: 2px solid #00a0e3; flex-wrap: wrap; }
        .tab { padding: 12px 20px; background: #eee; border: 1px solid #ccc; margin-right: 5px; cursor: pointer; font-size: 0.8rem; font-weight: bold; transition: 0.3s; }
        .tab:hover { background: #e0e0e0; }
        .tab.active { background: #00a0e3; color: white; border-bottom: none; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 2px solid #00a0e3; }
        th { background-color: #004080 ; color: white; padding: 12px; text-align: left; font-size: 0.9rem; border: 1px solid #ddd; }
        td { padding: 10px; border: 1px solid #ddd; font-size: 0.82rem; vertical-align: top; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .hidden { display: none; }
        .office-tag { display: block; font-weight: bold; color: #004080; margin-bottom: 4px; }

         /* ===== BURGER ICON & ANIMATION ===== */
        .burger {
            display: flex ;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
            z-index: 1001;
        }

        .burger span {
            height: 3px;
            width: 28px;
            background: white;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        /* Animation to transform burger into 'X' */
        .burger.toggle span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }
        .burger.toggle span:nth-child(2) {
            opacity: 0;
        }
        .burger.toggle span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        /* ===== RIGHT SIDEBAR ===== */
        .sidebar {
            flex: .7;
            margin-right: 150px;
            border: 1px solid #8aa6c1;
            padding: 15px;
            height: fit-content;
        }

        .sidebar h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .sidebar a {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #0b4a8b;
            text-decoration: none;
        }
        .sidebar a:hover {
            text-decoration: underline;
        }

        /* Footer */
        footer { background-color: #e6e6e6; padding: 30px; text-align: center; font-size: 0.8rem; border-top: 1px solid #ccc; margin-top: 50px; }
        
        /* ===== MOBILE RESPONSIVE LOGIC ===== */
        /*@media (max-width: 768px) {
            .top-nav {
                padding: 15px 20px;
            }*/

            @media (max-width: 480px) {
                .nav-links {
                    width: 70%; /* Takes up more space on small phones */
                }
            }

            .burger {
                display: flex;
            }

            .nav-links {
                position: fixed;
                right: -100%; /* Hidden off-screen by default */
                top: 0;
                height: 100vh;
                width: 190px; /* Fixed width for desktop consistency */
                background-color: #0b4a82; /* Matches the blue-grey in your screenshot */
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                padding-top: 80px; 
                gap: 0;
                transition: 0.3s ease-in-out;
                box-shadow: -5px 0 15px rgba(0,0,0,0.2);
            }

            .nav-links.active {
                right: 0;
            }

            .nav-links a {
                margin: 0;
                padding: 20px 30px;
                width: 100%;
                text-align: left;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                font-size: 18px;
            }
   </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<div class="top-nav">
        <div class="nav-brand">
            Department of Education<br>
            <small>Learning Information System</small>
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
            <a href="login.php">Login</a>
            </div>    
    </div>

<div class="container">
    <div class="contact-header">
        <div class="contact-text">
            <h2>Contact Us</h2>
            <p><strong>CONTACT</strong><br>
            sanjosedelmonte.city@deped.gov.ph</p>
            <p><strong>ADDRESS</strong><br>
            Eco Park, Muzon East, City of San Jose del Monte, Bulacan</p>
        </div>
        <div class="map-box">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3856.848803157297!2d121.0560!3d14.8143!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397bd08bb8b220b%3A0x11e6d66e6c46ec8e!2sSchools%20Division%20Office%20of%20City%20of%20San%20Jose%20del%20Monte!5e0!3m2!1sen!2sph!4v1700000000000" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>

    <div class="tabs">
        <div class="tab" onclick="switchTab(0)">OFFICE OF THE SCHOOLS DIVISION SUPERINTENDENT</div>
        <div class="tab active" onclick="switchTab(1)">CURRICULUM IMPLEMENTATION DIVISION</div>
        <div class="tab" onclick="switchTab(2)">SCHOOL GOVERNANCE & OPERATIONS DIVISION</div>
    </div>

    <table id="table-0" class="hidden">
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

    <table id="table-1">
        <thead>
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
        <thead><tr><th>OFFICE</th><th>HEAD OF OFFICE</th><th>CONTACT NO.</th></tr></thead>
        <tbody><tr><td colspan="3" style="text-align:center;"></td><tr>
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

<footer>
    <p>Eco Park Muzon East, City of San Jose del Monte, Bulacan 3023, Philippines<br>
    sanjosedelmonte.city@deped.gov.ph | (044) 305-7395<br>
    <strong>DepEd Tayo City of San Jose del Monte</strong></p>
</footer>

<script>
     const burger = document.getElementById('burger');
    const navMenu = document.getElementById('nav-menu');

    // Toggle menu and burger animation
    burger.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        burger.classList.toggle('toggle');
    });

    // Close menu when a link is clicked (useful for mobile)
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            burger.classList.remove('toggle');
        });
    });


    function switchTab(index) {
        let tabs = document.querySelectorAll('.tab');
        tabs.forEach((tab, i) => tab.classList.toggle('active', i === index));

        for (let i = 0; i < 3; i++) {
            const table = document.getElementById('table-' + i);
            if (table) table.classList.toggle('hidden', i !== index);
        }
    }
</script>

</body>
</html>