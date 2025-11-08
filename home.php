<?php
// Enforce authentication for user-facing pages
require_once 'includes/auth_guard.php';

// Include database connection
require_once 'connection/main_connection.php';

// Function to fetch service fields from database
function getServiceFields($conn, $serviceId)
{
    $fields = [];
    try {
        $stmt = $conn->prepare("
            SELECT f.field_id, f.label, f.field_type, f.is_required, f.display_order, f.allowed_file_types
            FROM services_fields f 
            WHERE f.service_id = ? 
            ORDER BY f.display_order ASC
        ");
        $stmt->bind_param('i', $serviceId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $field = $row;

            // If it's a select, radio, or checkbox field, get the options
            if (in_array($row['field_type'], ['select', 'radio', 'checkbox'])) {
                $optionsStmt = $conn->prepare("
                    SELECT option_label, option_value, display_order 
                    FROM services_field_options 
                    WHERE field_id = ? 
                    ORDER BY display_order ASC
                ");
                $optionsStmt->bind_param('i', $row['field_id']);
                $optionsStmt->execute();
                $optionsResult = $optionsStmt->get_result();

                $options = [];
                while ($optionRow = $optionsResult->fetch_assoc()) {
                    $options[] = $optionRow;
                }
                $field['options'] = $options;
            }

            $fields[] = $field;
        }
    } catch (Exception $e) {
        error_log("Error fetching service fields: " . $e->getMessage());
    }

    return $fields;
}

// Fetch active services from the database
$services = [];
try {
    $stmt = $conn->prepare("SELECT service_id, name, description, icon, button_text, is_active FROM services_list ORDER BY service_id ASC");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
} catch (Exception $e) {
    // If there's an error, we'll use an empty array and show a message
    error_log("Error fetching services: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Success Office - Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Material Design Color Palette - Dark Theme */
            --primary: #3b82f6;
            --primary-variant: #1d4ed8;
            --secondary: #10b981;
            --background: #f8fafc;
            --surface: #ffffff;
            --sidebar-bg: #1e293b;
            --sidebar-surface: #334155;
            --error: #ef4444;
            --warning: #f59e0b;
            --success: #10b981;
            --on-primary: #ffffff;
            --on-secondary: #ffffff;
            --on-background: #1e293b;
            --on-surface: #475569;
            --on-sidebar: #e2e8f0;
            --on-error: #ffffff;

            /* Material Design Elevations */
            --elevation-1: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            --elevation-2: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
            --elevation-3: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
            --elevation-4: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
            --elevation-5: 0 19px 38px rgba(0, 0, 0, 0.30), 0 15px 12px rgba(0, 0, 0, 0.22);
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--on-background);
            background: var(--background);
            min-height: 100vh;
            display: flex;
            margin: 0;
        }

        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            box-shadow: var(--elevation-3);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        .sidebar-header {
            padding: 24px 20px;
            background: var(--sidebar-bg);
            color: var(--on-sidebar);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            letter-spacing: 0.15px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-nav {
            list-style: none;
            padding: 16px 0;
        }

        .sidebar-nav li {
            margin-bottom: 0;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--on-sidebar);
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0.0, 0.2, 1);
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.1px;
            margin: 2px 12px;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            opacity: 0.8;
        }

        .sidebar-nav a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            opacity: 0;
            transition: opacity 0.2s cubic-bezier(0.4, 0.0, 0.2, 1);
            z-index: -1;
        }

        .sidebar-nav a:hover {
            opacity: 1;
        }

        .sidebar-nav a:hover::before {
            opacity: 1;
        }

        .sidebar-nav a.active {
            background: var(--primary);
            color: var(--on-primary);
            opacity: 1;
        }

        .sidebar-nav a.active::before {
            opacity: 0.12;
        }

        .sidebar-nav svg {
            width: 20px;
            height: 20px;
            stroke-width: 2;
        }

        .main-content {
            margin-left: 280px;
            padding: 32px;
            flex: 1;
            background: var(--background);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-header {
            margin-bottom: 32px;
            padding: 0;
            background: transparent;
            border-radius: 0;
            box-shadow: none;
            backdrop-filter: none;
            text-align: left;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            flex-wrap: wrap;
        }

        .header-text h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--on-background);
            margin: 0 0 8px 0;
            letter-spacing: -0.025em;
            text-shadow: none;
        }

        .header-text p {
            font-size: 16px;
            color: var(--on-surface);
            margin: 0;
            opacity: 0.7;
            max-width: none;
        }

        /* Search and Filter Styles */
        .search-filter-container {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
            background: var(--surface);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 0 16px;
            min-width: 280px;
            height: 48px;
            transition: all 0.2s ease;
        }

        .search-box:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        .search-box i {
            color: var(--on-surface);
            opacity: 0.6;
            margin-right: 12px;
            width: 20px;
            height: 20px;
        }

        .search-box input {
            flex: 1;
            border: none;
            background: transparent;
            color: var(--on-surface);
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            outline: none;
        }

        .search-box input::placeholder {
            color: var(--on-surface);
            opacity: 0.5;
        }

        .filter-dropdown {
            position: relative;
            display: flex;
            align-items: center;
            background: var(--surface);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            height: 48px;
            min-width: 160px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-dropdown:hover {
            border-color: var(--primary);
        }

        .filter-dropdown select {
            flex: 1;
            border: none;
            background: transparent;
            color: var(--on-surface);
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            padding: 0 16px;
            cursor: pointer;
            outline: none;
            appearance: none;
        }

        .filter-dropdown i {
            position: absolute;
            right: 12px;
            color: var(--on-surface);
            opacity: 0.6;
            width: 16px;
            height: 16px;
            pointer-events: none;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            margin-top: 32px;
            align-items: stretch;
        }

        .service-card {
            background: var(--surface);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--elevation-1);
            transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            text-align: left;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--elevation-3);
        }

        .service-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            flex-shrink: 0;
        }

        .service-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--primary-variant));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            box-shadow: none;
            flex-shrink: 0;
        }

        .service-icon svg {
            width: 24px;
            height: 24px;
            color: var(--on-primary);
            stroke-width: 2;
        }

        .service-info h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--on-background);
            margin: 0 0 4px 0;
            letter-spacing: -0.01em;
        }

        .service-info p {
            font-size: 14px;
            color: var(--on-surface);
            margin: 0;
            opacity: 0.7;
        }

        .service-description {
            font-size: 14px;
            line-height: 1.5;
            color: var(--on-surface);
            margin-bottom: 20px;
            opacity: 0.8;
            flex-grow: 1;
            min-height: 60px;
            display: flex;
            align-items: flex-start;
        }

        .service-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            flex-shrink: 0;
        }

        .service-btn,
        .cta-button {
            background: var(--primary);
            color: var(--on-primary);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0.0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: none;
        }

        .service-btn:hover,
        .cta-button:hover {
            background: var(--primary-variant);
            transform: translateY(-1px);
            box-shadow: var(--elevation-2);
        }

        .service-status {
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-available {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .service-btn.disabled {
            background: var(--surface-variant);
            color: var(--on-surface-variant);
            cursor: not-allowed;
            opacity: 0.6;
        }

        .service-btn.disabled:hover {
            background: var(--surface-variant);
            transform: none;
            box-shadow: var(--elevation-1);
        }

        .status-unavailable {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .footer {
            text-align: center;
            margin-top: 60px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .footer p {
            color: white;
            font-size: 1rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--surface);
            border: none;
            border-radius: 12px;
            padding: 12px;
            cursor: pointer;
            box-shadow: var(--elevation-2);
            color: var(--on-surface);
        }

        .mobile-menu-btn svg {
            width: 24px;
            height: 24px;
        }

        /* No Results Message */
        .no-results-message {
            grid-column: 1 / -1;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 300px;
            background: var(--surface);
            border-radius: 16px;
            border: 1px solid var(--outline-variant);
        }

        .no-results-content {
            text-align: center;
            color: var(--on-surface-variant);
        }

        .no-results-content i {
            width: 64px;
            height: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .no-results-content h3 {
            font-size: 20px;
            font-weight: 500;
            margin: 0 0 8px 0;
            color: var(--on-surface);
        }

        .no-results-content p {
            font-size: 14px;
            margin: 0;
            opacity: 0.7;
        }

        /* Responsive adjustments for search and filter */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .mobile-menu-btn {
                display: block;
            }

            .header-content {
                flex-direction: column;
                align-items: stretch;
                gap: 20px;
            }

            .search-filter-container {
                flex-direction: column;
                gap: 12px;
            }

            .search-box,
            .filter-dropdown {
                min-width: auto;
                width: 100%;
            }

            .dashboard-header h1 {
                font-size: 24px;
                margin-top: 60px;
            }

            .services-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .service-card {
                padding: 20px;
            }

            .service-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .service-icon {
                margin-right: 0;
            }

            .service-action {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }

            .service-status {
                align-self: flex-start;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 30px 15px;
            }

            .header h1 {
                font-size: 1.8rem;
            }

            .service-card {
                padding: 25px 15px;
            }

            .cta-button {
                padding: 10px 25px;
                font-size: 0.9rem;
            }

            .sidebar {
                width: 100%;
            }
        }

        /* Animation for page load */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .service-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .service-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .service-card:nth-child(2) {
            animation-delay: 0.2s;
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()">
        <i data-lucide="menu"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>
                <i data-lucide="graduation-cap"></i>
                Student Success
            </h2>
        </div>
        <nav>
            <ul class="sidebar-nav">
                <li>
                    <a href="#" class="active">
                        <i data-lucide="grid-3x3"></i>
                        Services
                    </a>
                </li>
                <li>
                    <a href="requests.php">
                        <i data-lucide="file-text"></i>
                        My Request
                    </a>
                </li>
                <li>
                    <a href="settings.php">
                        <i data-lucide="settings"></i>
                        Settings
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i data-lucide="log-out"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Dashboard Header -->
            <header class="dashboard-header">
                <div class="header-content">
                    <div class="header-text">
                        <h1>Services</h1>
                        <p>Welcome to Student Success Office - Your trusted partner in academic journey</p>
                    </div>

                    <!-- Search and Filter Controls -->
                    <div class="search-filter-container">
                        <div class="search-box">
                            <i data-lucide="search"></i>
                            <input type="text" id="serviceSearch" placeholder="Search services..." />
                        </div>

                        <div class="filter-dropdown">
                            <select id="availabilityFilter">
                                <option value="all">All Services</option>
                                <option value="available">Available Only</option>
                                <option value="unavailable">Unavailable Only</option>
                            </select>
                            <i data-lucide="chevron-down"></i>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Services Grid -->
            <div class="services-grid" id="servicesGrid">
                <?php if (empty($services)): ?>
                    <!-- No services available message -->
                    <div class="no-services-message" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                        <div style="color: var(--on-surface); opacity: 0.6;">
                            <i data-lucide="inbox" style="width: 48px; height: 48px; margin-bottom: 16px;"></i>
                            <h3 style="margin-bottom: 8px; font-weight: 500;">No Services Available</h3>
                            <p>Services are currently being updated. Please check back later.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <?php
                        // Determine availability status
                        $availability = $service['is_active'] ? 'available' : 'unavailable';
                        $statusText = $service['is_active'] ? 'Available' : 'Unavailable';
                        $statusClass = $service['is_active'] ? 'status-available' : 'status-unavailable';

                        // Handle icon - support both SVG content and Lucide icon names
                        $icon = !empty($service['icon']) ? $service['icon'] : 'file-text';
                        $isSvg = strpos($icon, '<svg') !== false;

                        // Default button text if none provided
                        $buttonText = !empty($service['button_text']) ? htmlspecialchars($service['button_text']) : 'Request Now';

                        // Create keywords for search functionality
                        $keywords = strtolower($service['name'] . ' ' . $service['description']);
                        ?>
                        <div class="service-card"
                            data-service-name="<?php echo htmlspecialchars($service['name']); ?>"
                            data-availability="<?php echo $availability; ?>"
                            data-keywords="<?php echo htmlspecialchars($keywords); ?>">
                            <div class="service-header">
                                <div class="service-icon">
                                    <?php if ($isSvg): ?>
                                        <?php echo $icon; ?>
                                    <?php else: ?>
                                        <i data-lucide="<?php echo htmlspecialchars($icon); ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="service-info">
                                    <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                                    <p>Service</p>
                                </div>
                            </div>

                            <div class="service-description">
                                <?php echo !empty($service['description']) ? htmlspecialchars($service['description']) : 'Service description not available.'; ?>
                            </div>

                            <div class="service-action">
                                <?php if ($service['is_active']): ?>
                                    <button class="service-btn" onclick="showServiceModal('<?php echo htmlspecialchars($service['name']); ?>', <?php echo $service['service_id']; ?>)">
                                        <i data-lucide="plus"></i>
                                        <?php echo $buttonText; ?>
                                    </button>
                                <?php else: ?>
                                    <button class="service-btn disabled" disabled>
                                        <i data-lucide="clock"></i>
                                        Temporarily Unavailable
                                    </button>
                                <?php endif; ?>
                                <span class="service-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/profile_guard.php'; ?>

    <!-- Include Modal -->
    <?php include "includes/modal.php"; ?>

    <!-- Include Loader -->
    <?php include "includes/loader.php"; ?>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');

            if (window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                !menuBtn.contains(event.target) &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });

        // Search and Filter Functionality
        const searchInput = document.getElementById('serviceSearch');
        const filterSelect = document.getElementById('availabilityFilter');
        const servicesGrid = document.getElementById('servicesGrid');
        const serviceCards = document.querySelectorAll('.service-card');

        // Search functionality
        function filterServices() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const filterValue = filterSelect.value;

            serviceCards.forEach(card => {
                const serviceName = card.getAttribute('data-service-name').toLowerCase();
                const keywords = card.getAttribute('data-keywords').toLowerCase();
                const availability = card.getAttribute('data-availability');

                // Check search match
                const searchMatch = searchTerm === '' ||
                    serviceName.includes(searchTerm) ||
                    keywords.includes(searchTerm);

                // Check filter match
                const filterMatch = filterValue === 'all' || availability === filterValue;

                // Show/hide card based on both search and filter
                if (searchMatch && filterMatch) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeInUp 0.3s ease forwards';
                } else {
                    card.style.display = 'none';
                }
            });

            // Show "no results" message if no cards are visible
            updateNoResultsMessage();
        }

        // Update no results message
        function updateNoResultsMessage() {
            const visibleCards = Array.from(serviceCards).filter(card =>
                card.style.display !== 'none'
            );

            let noResultsMsg = document.getElementById('noResultsMessage');

            if (visibleCards.length === 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.id = 'noResultsMessage';
                    noResultsMsg.className = 'no-results-message';
                    noResultsMsg.innerHTML = `
                        <div class="no-results-content">
                            <i data-lucide="search-x"></i>
                            <h3>No services found</h3>
                            <p>Try adjusting your search terms or filters</p>
                        </div>
                    `;
                    servicesGrid.appendChild(noResultsMsg);
                    lucide.createIcons(); // Re-initialize icons for the new element
                }
                noResultsMsg.style.display = 'flex';
            } else {
                if (noResultsMsg) {
                    noResultsMsg.style.display = 'none';
                }
            }
        }

        // Event listeners
        searchInput.addEventListener('input', filterServices);
        filterSelect.addEventListener('change', filterServices);

        // Clear search functionality
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                filterServices();
            }
        });

        // Service Modal Function
        function showServiceModal(serviceName, serviceId) {
            // Show loader while fetching service fields
            showLoader();

            // First, fetch the dynamic fields for this service
            fetch(`api/get-service-fields.php?service_id=${serviceId}`)
                .then(response => response.json())
                .then(data => {
                    // Hide loader once data is received
                    hideLoader();

                    if (data.error) {
                        showErrorMessage('Error loading service form: ' + data.error, 'Error');
                        return;
                    }

                    // If no fields are defined, show error modal and stop
                    if (!data.fields || data.fields.length === 0) {
                        showErrorMessage('No fields are configured for this service. Please try again later or contact support.', 'Service Unavailable');
                        return;
                    }

                    // Generate dynamic form HTML
                    let formHTML = `<div style="margin-top: 8px;">
                        <p style="margin-bottom: 16px; color: #6b7280;">You are requesting: <strong>${serviceName}</strong></p>`;

                    {
                        // Generate dynamic fields
                        data.fields.forEach(field => {
                            const fieldId = `field-${field.field_id}`;
                            const required = field.is_required ? 'required' : '';
                            const requiredText = field.is_required ? ' *' : '';
                            const visibleOptionId = field.visible_when_option_id || null;

                            // Wrap each field in a container used for conditional visibility
                            const initialDisplay = visibleOptionId ? 'none' : 'block';
                            formHTML += `<div id="field-container-${fieldId}" data-field-id="${fieldId}" data-visible-when-option-id="${visibleOptionId ?? ''}" style="display: ${initialDisplay}; margin-bottom: 16px; position: relative;">
                                <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #374151; font-size: 14px;">${field.label}${requiredText}</label>`;

                            switch (field.field_type) {
                                case 'text':
                                    formHTML += `<input type="text" id="${fieldId}" placeholder="Enter ${field.label.toLowerCase()}" style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: 'Poppins', sans-serif;" ${required} />`;
                                    break;

                                case 'email':
                                    formHTML += `<input type="email" id="${fieldId}" placeholder="Enter ${field.label.toLowerCase()}" style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: 'Poppins', sans-serif;" ${required} />`;
                                    break;

                                case 'number':
                                    formHTML += `<input type="number" id="${fieldId}" placeholder="Enter ${field.label.toLowerCase()}" style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: 'Poppins', sans-serif;" ${required} />`;
                                    break;

                                case 'textarea':
                                    formHTML += `<textarea id="${fieldId}" placeholder="Enter ${field.label.toLowerCase()}" style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: 'Poppins', sans-serif; min-height: 80px; resize: vertical;" ${required}></textarea>`;
                                    break;

                                case 'date':
                                    formHTML += `<input type="date" id="${fieldId}" style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: 'Poppins', sans-serif;" ${required} />`;
                                    break;

                                case 'select':
                                    formHTML += `<select id="${fieldId}" style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: 'Poppins', sans-serif;" ${required}>
                                        <option value="">Select ${field.label.toLowerCase()}</option>`;
                                    if (field.options) {
                                        field.options.forEach(option => {
                                            formHTML += `<option value="${option.option_value}" data-option-id="${option.option_id}">${option.option_label}</option>`;
                                        });
                                    }
                                    formHTML += `</select>`;
                                    break;

                                case 'radio':
                                    if (field.options) {
                                        field.options.forEach((option, index) => {
                                            formHTML += `<div style="margin-bottom: 8px;">
                                                <label style="display: flex; align-items: center; font-size: 14px; color: #374151;">
                                                    <input type="radio" name="${fieldId}" value="${option.option_value}" data-option-id="${option.option_id}" style="margin-right: 8px;" ${required && index === 0 ? 'required' : ''} />
                                                    ${option.option_label}
                                                </label>
                                            </div>`;
                                        });
                                    }
                                    break;

                                case 'checkbox':
                                    if (field.options) {
                                        field.options.forEach(option => {
                                            formHTML += `<div style="margin-bottom: 8px;">
                                                <label style="display: flex; align-items: center; font-size: 14px; color: #374151;">
                                                    <input type="checkbox" name="${fieldId}[]" value="${option.option_value}" data-option-id="${option.option_id}" style="margin-right: 8px;" />
                                                    ${option.option_label}
                                                </label>
                                            </div>`;
                                        });
                                    }
                                    break;

                                case 'file':
                                    const acceptTypes = field.allowed_file_types ? `accept="${field.allowed_file_types}"` : '';
                                    formHTML += `<input type="file" id="${fieldId}" style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: 'Poppins', sans-serif;" ${acceptTypes} ${required} />`;
                                    break;
                            }

                            formHTML += `</div>`; // end field-container
                        });
                    }

                    formHTML += `</div>`;

                    // Show the modal with dynamic form
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-plus"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M9 15h6"/><path d="M12 18v-6"/></svg>`,
                        iconBg: '#e0f2fe',
                        actionBtnBg: '#2E7D32',
                        showCancelBtn: true,
                        title: 'Request Service',
                        message: formHTML,
                        cancelText: 'Cancel',
                        actionText: 'Submit Request',
                        dismissOnConfirm: false,
                        onCancel: () => {
                            messageModalV1Dismiss();
                        },
                        onConfirm: () => {
                            // Validate and submit the dynamic form
                            validateAndSubmitDynamicForm(serviceId, serviceName, data.fields || []);
                        }
                    });

                    // Focus on first input after modal is shown
                    setTimeout(() => {
                        const firstInput = document.querySelector('#message-modalv1-confirm-modal input, #message-modalv1-confirm-modal textarea, #message-modalv1-confirm-modal select');
                        if (firstInput) {
                            firstInput.focus();
                        }
                    }, 100);

                    // Wire up conditional visibility after modal renders
                    setTimeout(() => {
                        setupFieldDependencies(data.fields || []);
                        attachFieldErrorClearHandlers(data.fields || []);
                    }, 0);
                })
                .catch(error => {
                    // Hide loader on error
                    hideLoader();
                    console.error('Error fetching service fields:', error);
                    showErrorMessage('Error loading service form. Please try again.', 'Error');
                });
        }

        // Tooltip helpers for field-level validation
        function ensureTooltipStyles() {
            if (document.getElementById('field-error-tooltip-styles')) return;
            const style = document.createElement('style');
            style.id = 'field-error-tooltip-styles';
            style.textContent = `
                .field-error-tooltip { position: absolute; top: 100%; left: 0; transform: translateY(4px); background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 6px; padding: 6px 8px; font-size: 12px; z-index: 30000; box-shadow: 0 4px 10px rgba(0,0,0,0.06); max-width: 420px; }
                .field-error-tooltip::after { content: ''; position: absolute; top: -6px; left: 10px; border-width: 6px; border-style: solid; border-color: transparent transparent #fee2e2 transparent; }
                .field-error-shake { animation: fieldErrorShake 160ms ease-in-out 0s 2; }
                @keyframes fieldErrorShake { 0%{ transform: translateX(0); } 25%{ transform: translateX(-2px); } 50%{ transform: translateX(2px);} 75%{ transform: translateX(-1px);} 100%{ transform: translateX(0);} }
            `;
            document.head.appendChild(style);
        }

        function showFieldErrorTooltip(fieldId, message) {
            ensureTooltipStyles();
            const container = document.getElementById(`field-container-${fieldId}`);
            if (!container) return;
            // Remove existing tooltip first
            clearFieldErrorTooltip(fieldId);
            const tip = document.createElement('div');
            tip.className = 'field-error-tooltip';
            tip.dataset.fieldId = fieldId;
            tip.innerHTML = message;
            container.appendChild(tip);
            container.classList.add('field-error-shake');
            setTimeout(() => container.classList.remove('field-error-shake'), 400);
        }

        function clearFieldErrorTooltip(fieldId) {
            const container = document.getElementById(`field-container-${fieldId}`);
            if (!container) return;
            const existing = container.querySelector('.field-error-tooltip');
            if (existing) existing.remove();
        }

        function clearAllFieldErrorTooltips() {
            document.querySelectorAll('#message-modalv1-confirm-modal .field-error-tooltip').forEach(el => el.remove());
        }

        function scrollAndFocusField(fieldId) {
            const modalRoot = document.getElementById('message-modalv1-confirm-modal');
            if (!modalRoot) return;
            const input = modalRoot.querySelector(`#${fieldId}, input[name="${fieldId}"], input[name="${fieldId}[]"], select#${fieldId}, textarea#${fieldId}`);
            const container = document.getElementById(`field-container-${fieldId}`) || input;
            if (container) {
                container.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
            if (input && typeof input.focus === 'function') {
                input.focus();
            }
        }

        function attachFieldErrorClearHandlers(fields) {
            const modalRoot = document.getElementById('message-modalv1-confirm-modal');
            if (!modalRoot) return;
            fields.forEach(field => {
                const fieldId = `field-${field.field_id}`;
                if (field.field_type === 'radio') {
                    const radios = modalRoot.querySelectorAll(`input[name="${fieldId}"]`);
                    radios.forEach(r => r.addEventListener('change', () => clearFieldErrorTooltip(fieldId)));
                } else if (field.field_type === 'checkbox') {
                    const checks = modalRoot.querySelectorAll(`input[name="${fieldId}[]"]`);
                    checks.forEach(c => c.addEventListener('change', () => clearFieldErrorTooltip(fieldId)));
                } else {
                    const input = document.getElementById(fieldId);
                    if (input) {
                        input.addEventListener('input', () => clearFieldErrorTooltip(fieldId));
                        input.addEventListener('change', () => clearFieldErrorTooltip(fieldId));
                    }
                }
            });
        }

        // Function to validate and submit dynamic form
        function validateAndSubmitDynamicForm(serviceId, serviceName, fields) {
            const formData = new FormData();
            let isValid = true;
            let firstErrorField = null;
            let hasAnyValue = false;
            let errorMessage = null;
            const errors = [];

            // Clear any previous tooltips
            clearAllFieldErrorTooltips();

            // If no fields defined, use default validation
            if (!fields || fields.length === 0) {
                const fullName = document.getElementById('service-fullname')?.value.trim();
                const email = document.getElementById('service-email')?.value.trim();
                const studentId = document.getElementById('service-student-id')?.value.trim();
                const notes = document.getElementById('service-notes')?.value.trim();

                if (!fullName) {
                    showErrorMessage('Please enter your full name.');
                    return;
                }
                if (!email) {
                    showErrorMessage('Please enter your email address.');
                    return;
                }
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showErrorMessage('Please enter a valid email address.');
                    return;
                }
                if (!studentId) {
                    showErrorMessage('Please enter your student ID.');
                    return;
                }

                // For default fields, just show success for now
                showSuccessMessage(serviceName);
                return;
            }

            // Utility: check if controlling option is active
            function isControllingOptionActive(optionId) {
                if (!optionId) return true;
                const modalRoot = document.getElementById('message-modalv1-confirm-modal');
                if (!modalRoot) return true;
                const optInput = modalRoot.querySelector(`[data-option-id="${optionId}"]`);
                if (!optInput) return false;
                if (optInput.tagName === 'OPTION') {
                    return optInput.selected === true;
                }
                if (optInput.type === 'checkbox' || optInput.type === 'radio') {
                    return optInput.checked === true;
                }
                return false;
            }

            // Validate dynamic fields
            fields.forEach(field => {
                const fieldId = `field-${field.field_id}`;
                const visibleOptionId = field.visible_when_option_id || null;

                // Skip validation/appending if field is conditionally hidden
                if (visibleOptionId && !isControllingOptionActive(visibleOptionId)) {
                    return; // hidden -> not applicable
                }

                if (field.field_type === 'radio') {
                    const radioInputs = document.querySelectorAll(`input[name="${fieldId}"]:checked`);
                    if (field.is_required && radioInputs.length === 0) {
                        isValid = false;
                        if (!firstErrorField) firstErrorField = field.label;
                        errors.push({
                            fieldId,
                            message: `${field.label} is required.`
                        });
                    } else if (radioInputs.length > 0) {
                        formData.append(fieldId, radioInputs[0].value);
                        hasAnyValue = true;
                    }
                } else if (field.field_type === 'checkbox') {
                    const checkboxInputs = document.querySelectorAll(`input[name="${fieldId}[]"]:checked`);
                    if (field.is_required && checkboxInputs.length === 0) {
                        isValid = false;
                        if (!firstErrorField) firstErrorField = field.label;
                        errors.push({
                            fieldId,
                            message: `${field.label} is required.`
                        });
                    } else {
                        checkboxInputs.forEach(checkbox => {
                            formData.append(`${fieldId}[]`, checkbox.value);
                        });
                        if (checkboxInputs.length > 0) {
                            hasAnyValue = true;
                        }
                    }
                } else {
                    const input = document.getElementById(fieldId);
                    if (input) {
                        const value = input.value.trim();
                        if (field.is_required && !value) {
                            isValid = false;
                            if (!firstErrorField) firstErrorField = field.label;
                            errors.push({
                                fieldId,
                                message: `${field.label} is required.`
                            });
                        } else if (value) {
                            if (field.field_type === 'file') {
                                if (input.files && input.files[0]) {
                                    const file = input.files[0];
                                    const allowed = (field.allowed_file_types || '')
                                        .split(',')
                                        .map(s => s.trim().toLowerCase())
                                        .filter(Boolean);
                                    const maxMb = field.max_file_size_mb ? Number(field.max_file_size_mb) : null;

                                    const fileIssues = [];
                                    if (allowed.length) {
                                        const ext = '.' + (file.name.split('.').pop() || '').toLowerCase();
                                        if (!allowed.includes(ext)) {
                                            fileIssues.push(`${field.label}: only ${allowed.join(', ')} files are allowed.`);
                                        }
                                    }
                                    if (maxMb && Number.isFinite(maxMb)) {
                                        const maxBytes = maxMb * 1024 * 1024;
                                        if (file.size > maxBytes) {
                                            fileIssues.push(`${field.label}: file exceeds ${maxMb} MB size limit.`);
                                        }
                                    }

                                    if (fileIssues.length) {
                                        isValid = false;
                                        if (!firstErrorField) firstErrorField = field.label;
                                        fileIssues.forEach(msg => errors.push({
                                            fieldId,
                                            message: msg
                                        }));
                                    } else {
                                        formData.append(fieldId, file);
                                        hasAnyValue = true;
                                    }
                                }
                            } else if (field.field_type === 'email') {
                                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                                if (!emailRegex.test(value)) {
                                    isValid = false;
                                    if (!firstErrorField) firstErrorField = field.label;
                                    errors.push({
                                        fieldId,
                                        message: `Please enter a valid email for: ${field.label}`
                                    });
                                } else {
                                    formData.append(fieldId, value);
                                    hasAnyValue = true;
                                }
                            } else if (field.field_type === 'number') {
                                const numVal = Number(value);
                                if (Number.isNaN(numVal)) {
                                    isValid = false;
                                    if (!firstErrorField) firstErrorField = field.label;
                                    errors.push({
                                        fieldId,
                                        message: `Please enter a valid number for: ${field.label}`
                                    });
                                } else {
                                    formData.append(fieldId, value);
                                    hasAnyValue = true;
                                }
                            } else {
                                formData.append(fieldId, value);
                                hasAnyValue = true;
                            }
                        }
                    }
                }
            });

            if (!isValid) {
                // Show field-level tooltips instead of modal
                const modalRoot = document.getElementById('message-modalv1-confirm-modal');
                if (modalRoot) {
                    errors.forEach(err => {
                        if (err && err.fieldId) {
                            showFieldErrorTooltip(err.fieldId, err.message);
                        }
                    });
                    // Focus and scroll to the first error field
                    if (errors.length > 0 && errors[0].fieldId) {
                        scrollAndFocusField(errors[0].fieldId);
                    }
                }
                return;
            }

            // If none of the dynamic fields have any value (all optional and empty), show error modal
            if (fields && fields.length > 0 && !hasAnyValue) {
                // Attach a tooltip to the first field prompting user to provide input
                const firstFieldId = `field-${fields[0].field_id}`;
                showFieldErrorTooltip(firstFieldId, 'Please complete at least one field before submitting.');
                scrollAndFocusField(firstFieldId);
                return;
            }

            // Add service ID to form data
            formData.append('service_id', serviceId);

            // Show loader while submitting
            showLoader();

            // Submit the form data to the server
            fetch('api/submit-service-request.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loader after response
                    hideLoader();

                    if (data.error) {
                        showErrorMessage('Error submitting request: ' + data.error, 'Submission Error');
                    } else {
                        showSuccessMessage(data.service_name, data.request_id);
                    }
                })
                .catch(error => {
                    // Hide loader on error
                    hideLoader();
                    console.error('Error submitting form:', error);
                    showErrorMessage('Error submitting request. Please try again.', 'Submission Error');
                });
        }

        // Setup conditional field visibility based on option selection
        function setupFieldDependencies(fields) {
            const modalRoot = document.getElementById('message-modalv1-confirm-modal');
            if (!modalRoot) return;

            // Map optionId -> dependent containers
            const depsMap = {};
            fields.forEach(f => {
                const optId = f.visible_when_option_id || null;
                if (!optId) return;
                const containerId = `field-container-field-${f.field_id}`;
                const containerEl = document.getElementById(containerId);
                // Backward compatibility: our container id uses fieldId string above
                const containerEl2 = document.getElementById(`field-container-${"field-" + f.field_id}`) || document.getElementById(`field-container-${containerId}`);
                const finalContainer = containerEl2 || containerEl;
                if (!finalContainer) return;
                if (!depsMap[optId]) depsMap[optId] = [];
                depsMap[optId].push(finalContainer);
            });

            // Helper to update display for a given option id
            function updateDisplayForOption(optionId) {
                const activeEl = modalRoot.querySelector(`[data-option-id="${optionId}"]`);
                let isActive = false;
                if (activeEl) {
                    if (activeEl.tagName === 'OPTION') {
                        // Ensure we evaluate selection from its select
                        isActive = activeEl.selected === true;
                    } else if (activeEl.type === 'checkbox' || activeEl.type === 'radio') {
                        isActive = activeEl.checked === true;
                    }
                }
                const targets = depsMap[optionId] || [];
                targets.forEach(el => {
                    el.style.display = isActive ? 'block' : 'none';
                });
            }

            // Attach listeners for each controlling element
            Object.keys(depsMap).forEach(optionId => {
                const controls = modalRoot.querySelectorAll(`[data-option-id="${optionId}"]`);
                controls.forEach(ctrl => {
                    if (ctrl.tagName === 'OPTION') {
                        const selectEl = ctrl.closest('select');
                        if (selectEl) {
                            selectEl.addEventListener('change', () => updateDisplayForOption(optionId));
                        }
                    } else if (ctrl.type === 'checkbox' || ctrl.type === 'radio') {
                        const groupName = ctrl.name;
                        const groupInputs = modalRoot.querySelectorAll(`input[name="${groupName}"]`);
                        groupInputs.forEach(inp => {
                            inp.addEventListener('change', () => updateDisplayForOption(optionId));
                        });
                    }
                });
                // Initial evaluation
                updateDisplayForOption(optionId);
            });
        }

        // Function to show error message in a modal
        function showErrorMessage(message, title = 'Validation Error', onOk = null) {
            messageModalV1Show({
                icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
                iconBg: '#fee2e2',
                actionBtnBg: '#b91c1c',
                showCancelBtn: false,
                title: title,
                message: message,
                actionText: 'OK',
                dismissOnConfirm: true,
                onConfirm: () => {
                    messageModalV1Dismiss();
                    if (typeof onOk === 'function') {
                        try {
                            onOk();
                        } catch (e) {
                            console.error(e);
                        }
                    }
                }
            });
        }

        // Function to show success message
        function showSuccessMessage(serviceName, requestId = null) {
            messageModalV1Dismiss();

            const requestIdText = requestId ? ` Your request ID is <strong>#${requestId}</strong>.` : '';

            setTimeout(() => {
                messageModalV1Show({
                    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 12 2 2 4-4"/></svg>`,
                    iconBg: '#dcfce7',
                    actionBtnBg: '#16a34a',
                    showCancelBtn: false,
                    title: 'Request Submitted',
                    message: `Your request for <strong>${serviceName}</strong> has been submitted successfully.${requestIdText} You will receive a confirmation email shortly.`,
                    actionText: 'OK',
                    dismissOnConfirm: true,
                    onConfirm: () => {
                        messageModalV1Dismiss();
                    }
                });
            }, 300);
        }
    </script>
</body>

</html>