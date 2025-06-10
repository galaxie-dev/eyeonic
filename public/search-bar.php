<head>
<style>
    :root {
    --primary: #2563eb;
    --primary-light: #3b82f6;
    --primary-dark: #1d4ed8;
    --secondary: #e0f2fe;
    --dark: #1e293b;
    --light: #f8fafc;
    --accent: #f43f5e;
    --success: #10b981;
}
.search-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    transition: all 0.3s ease;
}
.search-form {
    max-width: 600px;
    margin: 0 auto 0.6rem auto;
    position: relative;
    display: flex;
}
.search-form.sticky {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        max-width: 100%;
        padding: 0.5rem 1rem;
        background: white;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin: 0;
        border-radius: 0;
    }
.sticky .search-form-inner {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }

.search-form input[type="text"] {
    width: 100%;
    padding: 0.8rem 1.2rem;
    padding-right: 3rem;
    border: 1px solid #e2e8f0;
    border-radius: 15px;
    font-size: 1rem;
    outline: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.search-form input[type="text"]:focus {
    border-color: #2563eb;
    box-shadow: 0 2px 10px rgba(37, 99, 235, 0.1);
}

.search-form button {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    padding: 0 1.5rem;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 0 15px 15px 0;
    cursor: pointer;
    transition: background 0.3s ease;
}

.search-form button:hover {
    background: #1d4ed8;
}

/* Mobile Styles */
@media (max-width: 768px) {
    .search-form {
        margin: 0 auto 0.5rem auto;
        padding: 0 0.5rem;
    }
     .search-form.sticky {
            padding: 0.5rem;
        }
    
    .search-form input[type="text"] {
        padding: 0.7rem 1rem;
        font-size: 0.9rem;
    }
    
    .search-form button {
        padding: 0 1rem;
    }
}
</style>
</head>


<body>

<div class="search-container">
    <form method="get" class="search-form" id="sticky-search">
        <div class="search-form-inner">
            <input type="text" name="q" placeholder="Search for eyewear..." 
                value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button type="submit">Search</button>
        </div>
    </form>
</div>

<script>   
    window.addEventListener('scroll', function() {
        const searchForm = document.getElementById('sticky-search');
        const scrollPosition = window.scrollY || document.documentElement.scrollTop;
        
        if (scrollPosition > 135) {
            searchForm.classList.add('sticky');
        } else {
            searchForm.classList.remove('sticky');
        }
    });
</script>
</body>