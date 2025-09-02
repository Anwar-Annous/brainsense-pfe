<?php
require_once 'includes/document_functions.php';

session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];


// Query to get secretary ID based on user ID
$secretary_query = "SELECT id FROM secretaries WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $secretary_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$secretary_row = mysqli_fetch_assoc($result);

// Check if secretary exists
if (!$secretary_row) {
    // Handle case where secretary is not found
    $secretary_name = 'Unknown Secretary';
    $secretary_photo = 'assets/images/default-avatar.png';
} else {
    $secretary_id = $secretary_row['id'];
    // Query to get secretary name and profile picture
    $secretary_details_query = "SELECT full_name, profile_photo FROM secretaries WHERE id = ?";
    $stmt = mysqli_prepare($conn, $secretary_details_query);
    mysqli_stmt_bind_param($stmt, "i", $secretary_id);
    mysqli_stmt_execute($stmt);
    $secretary_details = mysqli_stmt_get_result($stmt);
    $secretary = mysqli_fetch_assoc($secretary_details);

    // Store secretary details in variables with error handling
    $secretary_name = $secretary ? $secretary['full_name'] : 'Unknown Secretary';
    $secretary_photo = ($secretary && $secretary['profile_photo']) ? $secretary['profile_photo'] : 'assets/images/default-avatar.png';
}

$stats = getDocumentStats();
$categories = getDocumentCategories();
$documents = getDocuments();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Center - BrainSense</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <img src="Logo(BrainSense )png.png" width="70px" alt="BrainSense Logo">
            <span class="Logo">BrainSense</span>
        </a>
        <ul class="side-menu top">
            <li>
                <a href="index.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="patient_records.php">
                    <i class='bx bxs-user'></i>
                    <span class="text">Patient Records</span>
                </a>
            </li>
            <li class="active">
                <a href="document_center.php">
                    <i class='bx bxs-file'></i>
                    <span class="text">Document Center</span>
                </a>
            </li>
            <li>
                <a href="communication_hub.php">
                    <i class='bx bxs-chat'></i>
                    <span class="text">Communication Hub</span>
                </a>
            </li>
            <li>
                <a href="resources.php">
                    <i class='bx bxs-clinic'></i>
                    <span class="text">Resources</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">

            <li>
                <a href="../logout.php" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link">Categories</a>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="switch-mode" class="switch-mode"></label>
			<a href="communication_hub.php" class="notification">
                <i class='bx bxs-bell'></i>
            </a>
            <a href="#" class="profile">
                <img src="<?php echo isset($secretary_photo) ? htmlspecialchars($secretary_photo) : 'assets/images/default-avatar.png'; ?>" alt="Profile Picture">
                <span class="profile-name" title="<?php echo isset($secretary_name) ? htmlspecialchars($secretary_name) : 'Unknown Secretary'; ?>">
                    <?php echo isset($secretary_name) ? htmlspecialchars($secretary_name) : 'Unknown Secretary'; ?>
                </span>
            </a>
        </nav>

        <div class="document-center">
            <!-- Header Section -->
            <div class="documents-header">
                <div class="header-left">
                    <h2><i class='bx bxs-file'></i> Document Center</h2>
                    <p class="subtitle">Manage and organize medical documents and reports</p>
                </div>
                <div class="header-actions">
                    <button class="btn-secondary" onclick="openUploadModal()">
                        <i class='bx bx-upload'></i> Upload Document
                    </button>
                    <button class="btn-secondary" onclick="openNewCategoryModal()">
                        <i class='bx bx-folder-plus'></i> New Category
                    </button>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class='bx bxs-file-pdf'></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Documents</h3>
                        <p><?php echo $stats['total_documents']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class='bx bxs-folder'></i>
                    </div>
                    <div class="stat-info">
                        <h3>Categories</h3>
                        <p><?php echo count($categories); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class='bx bxs-cloud-upload'></i>
                    </div>
                    <div class="stat-info">
                        <h3>Storage Used</h3>
                        <p><?php echo formatFileSize($stats['total_size']); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class='bx bxs-time'></i>
                    </div>
                    <div class="stat-info">
                        <h3>Recent Uploads</h3>
                        <p><?php echo $stats['recent_uploads']; ?> Today</p>
                    </div>
                </div>
            </div>

            <!-- Document Management -->
            <div class="document-management">
                <!-- Left Sidebar -->
                <div class="doc-sidebar">
                    <div class="search-box">
                        <i class='bx bx-search'></i>
                        <input type="text" id="searchInput" placeholder="Search documents...">
                    </div>
                    
                    <div class="folder-tree">
                        <h3>Categories</h3>
                        <ul class="folder-list">
                            <li class="active" data-category="">
                                <i class='bx bxs-folder'></i>
                                <span>All Documents</span>
                                <span class="count"><?php echo $stats['total_documents']; ?></span>
                            </li>
                            <?php foreach ($categories as $category): ?>
                            <li data-category="<?php echo $category['id']; ?>">
                                <i class='bx bxs-folder'></i>
                                <span><?php echo htmlspecialchars($category['name']); ?></span>
                                <span class="count"><?php 
                                    $count = 0;
                                    foreach ($stats['categories'] as $cat) {
                                        if ($cat['name'] === $category['name']) {
                                            $count = $cat['count'];
                                            break;
                                        }
                                    }
                                    echo $count;
                                ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="doc-content">
                    <div class="content-header">
                        <div class="breadcrumb">
                            <span>All Documents</span>
                        </div>
                        <div class="view-options">
                            <button class="view-btn active" data-view="grid">
                                <i class='bx bx-grid-alt'></i>
                            </button>
                            <button class="view-btn" data-view="list">
                                <i class='bx bx-list-ul'></i>
                            </button>
                        </div>
                    </div>

                    <div class="documents-grid" id="documentsGrid">
                        <?php foreach ($documents as $doc): ?>
                        <div class="document-card" data-id="<?php echo $doc['id']; ?>" onclick="previewDocument(<?php echo $doc['id']; ?>, '<?php echo htmlspecialchars($doc['name']); ?>', '<?php echo htmlspecialchars($doc['file_path']); ?>', '<?php echo htmlspecialchars($doc['file_type']); ?>')">
                            <div class="doc-icon">
                                <i class='bx <?php echo getFileIcon($doc['file_type']); ?>'></i>
                            </div>
                            <div class="doc-info">
                                <h4><?php echo htmlspecialchars($doc['name']); ?></h4>
                                <p><?php echo formatFileSize($doc['file_size']); ?> • <?php echo formatDate($doc['created_at']); ?></p>
                                <?php if ($doc['patient_name']): ?>
                                <p class="patient-name">Patient: <?php echo htmlspecialchars($doc['patient_name']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="doc-actions">
                                <button class="action-btn" title="Download" onclick="event.stopPropagation(); downloadDocument(<?php echo $doc['id']; ?>)">
                                    <i class='bx bx-download'></i>
                                </button>
                                <button class="action-btn" title="Delete" onclick="event.stopPropagation(); deleteDocument(<?php echo $doc['id']; ?>)">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upload Document Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class='bx bx-upload'></i> Upload Document</h2>
            
            <form id="uploadForm" class="upload-form">
                <div class="upload-area" id="uploadArea">
                    <div class="upload-preview" id="uploadPreview" style="display: none;">
                        <div class="file-info">
                            <i class='bx bxs-file'></i>
                            <div class="file-details">
                                <span class="file-name" id="fileName"></span>
                                <span class="file-size" id="fileSize"></span>
                            </div>
                        </div>
                        <button type="button" class="remove-file" onclick="removeSelectedFile()">
                            <i class='bx bx-x'></i>
                        </button>
                    </div>
                    <div class="upload-placeholder" id="uploadPlaceholder">
                        <i class='bx bx-cloud-upload'></i>
                        <h3>Drag & Drop Files Here</h3>
                        <p>or</p>
                        <input type="file" id="fileInput" name="file" accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.csv" style="display: none;">
                        <button type="button" class="btn-secondary" onclick="document.getElementById('fileInput').click()">
                            Browse Files
                        </button>
                        <p class="upload-hint">Max file size: 10MB<br>Allowed types: PDF, Images, Word, Excel, CSV</p>
                    </div>
                </div>

                <div class="form-group">
                    <label>Document Name</label>
                    <input type="text" name="name" required placeholder="Enter document name">
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Patient (Optional)</label>
                    <select name="patient_id">
                        <option value="">No Patient Selected</option>
                        <?php
                        $patients_query = "SELECT id, full_name FROM patients ORDER BY full_name";
                        $patients_result = mysqli_query($conn, $patients_query);
                        while ($patient = mysqli_fetch_assoc($patients_result)) {
                            echo '<option value="' . $patient['id'] . '">' . htmlspecialchars($patient['full_name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Enter document description"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeUploadModal()">
                        <i class='bx bx-x'></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class='bx bx-upload'></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- New Category Modal -->
    <div id="newCategoryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeNewCategoryModal()">&times;</span>
            <h2><i class='bx bx-folder-plus'></i> Create New Category</h2>
            
            <form id="newCategoryForm" class="category-form">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" required placeholder="Enter category name">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Enter category description"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeNewCategoryModal()">
                        <i class='bx bx-x'></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class='bx bx-save'></i> Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="modal">
        <div class="modal-content preview-content">
            <span class="close" onclick="closePreviewModal()">&times;</span>
            <div class="preview-header">
                <h2 id="previewTitle"></h2>
                <div class="preview-actions">
                    <button class="btn-secondary" onclick="downloadCurrentDocument()">
                        <i class='bx bx-download'></i> Download
                    </button>
                </div>
            </div>
            <div class="preview-body">
                <div id="previewContainer"></div>
            </div>
        </div>
    </div>

    <style>
        /* Layout Adjustments */
        .document-center {
            padding: 1.5rem;
            margin-top: 0.5rem;
            height: calc(100vh - 70px);
            overflow-y: auto;
        }

        /* Header Section */
        .documents-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: var(--light);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .header-left h2 {
            font-size: 1.5rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-left .subtitle {
            color: var(--dark-grey);
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--light);
            padding: 1.25rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Document Management */
        .document-management {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 1.5rem;
            background: var(--light);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            height: calc(100vh - 300px);
        }

        /* Sidebar Styles */
        .doc-sidebar {
            background: var(--light);
            padding: 1.25rem;
            border-right: 1px solid var(--grey);
            height: 100%;
            overflow-y: auto;
        }

        .search-box {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--grey);
            border-radius: 8px;
            font-size: 0.9rem;
            background: var(--light);
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
        }

        /* Folder Tree */
        .folder-list li {
            padding: 0.75rem 1rem;
            margin-bottom: 0.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .folder-list li:hover {
            background: var(--light-blue);
        }

        .folder-list li.active {
            background: var(--blue);
            color: var(--light);
        }

        .folder-list li .count {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        /* Document Grid */
        .doc-content {
            padding: 1.25rem;
            height: 100%;
            overflow-y: auto;
        }

        .content-header {
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--grey);
        }

        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
            padding: 0.5rem;
        }

        .document-card {
            background: var(--light);
            border: 1px solid var(--grey);
            border-radius: 12px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .document-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: var(--blue);
        }

        /* Upload Modal */
        .modal-content {
            max-width: 600px;
            width: 90%;
            background: var(--light);
            border-radius: 12px;
            padding: 2rem;
        }

        /* Upload Area Styles */
        .upload-area {
            position: relative;
            border: 2px dashed var(--grey);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            background: var(--light);
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .upload-area.highlight {
            border-color: var(--blue);
            background: var(--light-blue);
        }

        .upload-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .upload-preview {
            width: 100%;
            padding: 1rem;
            background: var(--light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        .file-info i {
            font-size: 2rem;
            color: var(--blue);
        }

        .file-details {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.25rem;
        }

        .file-name {
            font-weight: 500;
            color: var(--dark);
            word-break: break-all;
        }

        .file-size {
            font-size: 0.875rem;
            color: var(--dark-grey);
        }

        .remove-file {
            background: none;
            border: none;
            color: var(--dark-grey);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .remove-file:hover {
            background: var(--grey);
            color: var(--dark);
        }

        body.dark .upload-area {
            background: var(--dark);
            border-color: var(--dark-grey);
        }

        body.dark .upload-preview {
            background: var(--dark);
        }

        body.dark .file-name {
            color: var(--light);
        }

        body.dark .file-size {
            color: var(--light-grey);
        }

        body.dark .remove-file {
            color: var(--light-grey);
        }

        body.dark .remove-file:hover {
            background: var(--dark-grey);
            color: var(--light);
        }

        /* Dark Mode Adjustments */
        body.dark .document-center {
            background: var(--dark);
        }

        body.dark .documents-header,
        body.dark .stat-card,
        body.dark .document-management,
        body.dark .doc-sidebar,
        body.dark .document-card {
            background: var(--dark);
            border-color: var(--dark-grey);
        }

        body.dark .search-box input {
            background: var(--dark);
            border-color: var(--dark-grey);
            color: var(--light);
        }

        /* Mobile Responsiveness */
        @media screen and (max-width: 768px) {
            .document-management {
                grid-template-columns: 1fr;
                height: auto;
            }

            .doc-sidebar {
                border-right: none;
                border-bottom: 1px solid var(--grey);
                height: auto;
                max-height: 300px;
            }

            .doc-content {
                height: auto;
            }

            .documents-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .header-actions {
                width: 100%;
                justify-content: center;
            }

            .quick-stats {
                grid-template-columns: 1fr;
            }
        }

        /* Add styles for no documents message */
        .no-documents {
            grid-column: 1 / -1;
            text-align: center;
            padding: 2rem;
            color: var(--dark-grey);
            font-size: 1.1rem;
            background: var(--light);
            border-radius: 12px;
            border: 1px dashed var(--grey);
        }

        body.dark .no-documents {
            background: var(--dark);
            border-color: var(--dark-grey);
            color: var(--light-grey);
        }

        /* New Category Modal Styles */
        .category-form {
            margin-top: 1.5rem;
        }

        .category-form .form-group {
            margin-bottom: 1.25rem;
        }

        .category-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .category-form input,
        .category-form textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--grey);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .category-form input:focus,
        .category-form textarea:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
            outline: none;
        }

        body.dark .category-form label {
            color: var(--light);
        }

        body.dark .category-form input,
        body.dark .category-form textarea {
            background: var(--dark);
            border-color: var(--dark-grey);
            color: var(--light);
        }

        body.dark .category-form input:focus,
        body.dark .category-form textarea:focus {
            border-color: var(--blue);
        }

        /* Header Actions Spacing */
        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .header-actions .btn-secondary {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Add styles for alerts */
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert i {
            font-size: 1.25rem;
        }
        
        body.dark .alert-success {
            background-color: #1e4620;
            color: #d4edda;
            border-color: #2a5a2a;
        }
        
        body.dark .alert-error {
            background-color: #462020;
            color: #f8d7da;
            border-color: #5a2a2a;
        }

        /* Add styles for file input */
        .file-input-wrapper {
            position: relative;
            width: 100%;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .upload-area {
            position: relative;
            border: 2px dashed var(--grey);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            background: var(--light);
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: var(--blue);
            background: var(--light-blue);
        }

        body.dark .upload-area {
            background: var(--light);
            border-color: var(--dark-grey);
        }

        body.dark .upload-area:hover {
            background: var(--dark-blue);
        }

        .preview-content {
            max-width: 90%;
            width: 90%;
            height: 90vh;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .preview-header {
            padding: 1rem;
            border-bottom: 1px solid var(--grey);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .preview-body {
            flex: 1;
            overflow: auto;
            padding: 1rem;
            background: var(--light);
        }

        .preview-container {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .preview-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .preview-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .preview-container .unsupported-file {
            text-align: center;
            padding: 2rem;
            color: var(--dark-grey);
        }

        body.dark .preview-body {
            background: var(--dark);
        }

        body.dark .preview-header {
            border-color: var(--dark-grey);
        }

        .document-card {
            cursor: pointer;
        }

        .document-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: var(--blue);
        }

        /* Profile section styling */
        .profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            border-radius: 50px;
            background: var(--light);
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--dark);
        }

        .profile:hover {
            background: var(--grey);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .profile-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--dark);
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: all 0.3s ease;
        }

        /* Dark mode support */
        body.dark .profile {
            background: var(--light);
            color: var(--dark);
        }

        body.dark .profile:hover {
            background: var(--grey);
        }

        body.dark .profile-name {
            color: white ;
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .profile {
                padding: 6px 12px;
            }

            .profile img {
                width: 32px;
                height: 32px;
            }

            .profile-name {
                max-width: 120px;
                font-size: 0.9rem;
            }
        }
    </style>
    <script src="script.js"></script>

    <script>
        // Add sidebar toggle functionality
        document.querySelector('.bx-menu').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hide');
        });

        // Dark mode switch logic
        document.addEventListener('DOMContentLoaded', function() {
            const switchMode = document.getElementById('switch-mode');
            // Load dark mode preference
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark');
                switchMode.checked = true;
            }
            switchMode.addEventListener('change', function () {
                if (this.checked) {
                    document.body.classList.add('dark');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    document.body.classList.remove('dark');
                    localStorage.setItem('darkMode', 'disabled');
                }
            });
        });

        // Function to refresh stats
        function refreshStats() {
            fetch('api/document_operations.php?action=stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update total documents
                        document.querySelector('.stat-card:nth-child(1) .stat-info p').textContent = data.stats.total_documents;
                        
                        // Update categories count
                        document.querySelector('.stat-card:nth-child(2) .stat-info p').textContent = data.stats.categories.length;
                        
                        // Update storage used
                        document.querySelector('.stat-card:nth-child(3) .stat-info p').textContent = formatFileSize(data.stats.total_size);
                        
                        // Update recent uploads
                        document.querySelector('.stat-card:nth-child(4) .stat-info p').textContent = data.stats.recent_uploads + ' Today';
                        
                        // Update category counts
                        const categoryItems = document.querySelectorAll('.folder-list li');
                        categoryItems.forEach(item => {
                            const categoryName = item.querySelector('span').textContent;
                            if (categoryName === 'All Documents') {
                                item.querySelector('.count').textContent = data.stats.total_documents;
                            } else {
                                const category = data.stats.categories.find(cat => cat.name === categoryName);
                                if (category) {
                                    item.querySelector('.count').textContent = category.count;
                                }
                            }
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Initialize document center functionality
        document.addEventListener('DOMContentLoaded', function() {
            // View toggle functionality
            const viewButtons = document.querySelectorAll('.view-btn');
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    viewButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    const view = this.dataset.view;
                    document.getElementById('documentsGrid').className = 
                        view === 'list' ? 'documents-list' : 'documents-grid';
                });
            });

            // Category filter
            const categoryItems = document.querySelectorAll('.folder-list li');
            categoryItems.forEach(item => {
                item.addEventListener('click', function() {
                    categoryItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    loadDocuments(this.dataset.category);
                });
            });

            // Search functionality
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadDocuments(
                        document.querySelector('.folder-list li.active').dataset.category,
                        this.value
                    );
                }, 300);
            });

            // File upload functionality
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('fileInput');
            const uploadPlaceholder = document.getElementById('uploadPlaceholder');
            const uploadPreview = document.getElementById('uploadPreview');

            // Make the entire upload area clickable
            uploadArea.addEventListener('click', function(e) {
                if (e.target === uploadArea || e.target === uploadPlaceholder) {
                    fileInput.click();
                }
            });

            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                if (this.files.length > 0) {
                    handleFileSelect(this.files[0]);
                }
            });

            // Drag and drop handlers
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });

            uploadArea.addEventListener('drop', handleDrop, false);

            // Upload form submission
            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const fileInput = document.getElementById('fileInput');
                if (!fileInput.files || !fileInput.files[0]) {
                    showError('Please select a file to upload');
                    return;
                }

                const formData = new FormData(this);
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Uploading...';
                submitBtn.disabled = true;
                
                fetch('api/document_operations.php?action=upload', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const successMessage = document.createElement('div');
                        successMessage.className = 'alert alert-success';
                        successMessage.innerHTML = '<i class="bx bx-check-circle"></i> ' + (data.message || 'Document uploaded successfully');
                        document.querySelector('.modal-content').insertBefore(successMessage, this);
                        
                        // Close modal after 2 seconds
                        setTimeout(() => {
                            closeUploadModal();
                            loadDocuments();
                            refreshStats();
                        }, 2000);
                    } else {
                        showError(data.error || 'Upload failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Upload failed. Please try again.');
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                });
            });

            // New Category Form Submission
            document.getElementById('newCategoryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('api/document_operations.php?action=create_category', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeNewCategoryModal();
                        // Refresh the page to show new category
                        location.reload();
                    } else {
                        alert('Failed to create category: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to create category. Please try again.');
                });
            });
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(e) {
            document.getElementById('uploadArea').classList.add('highlight');
        }

        function unhighlight(e) {
            document.getElementById('uploadArea').classList.remove('highlight');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        }

        function handleFileSelect(file) {
            if (!file) {
                showError('Please select a file to upload');
                return;
            }

            // Validate file size (max 10MB)
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if (file.size > maxSize) {
                showError('File size exceeds 10MB limit');
                return;
            }

            // Validate file type
            const allowedTypes = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/csv',
                'application/csv',
                'text/comma-separated-values',
                'application/vnd.ms-excel'
            ];
            
            if (!allowedTypes.includes(file.type)) {
                showError('Invalid file type. Allowed types: PDF, Images, Word, Excel, CSV');
                return;
            }

            // Show file preview
            document.getElementById('uploadPlaceholder').style.display = 'none';
            document.getElementById('uploadPreview').style.display = 'flex';
            
            // Update file info
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = formatFileSize(file.size);
            
            // Update file icon based on type
            const fileIcon = document.querySelector('.file-info i');
            fileIcon.className = 'bx ' + getFileIcon(file.type);
        }

        function showError(message) {
            const errorMessage = document.createElement('div');
            errorMessage.className = 'alert alert-error';
            errorMessage.innerHTML = '<i class="bx bx-error-circle"></i> ' + message;
            document.querySelector('.modal-content').insertBefore(errorMessage, document.getElementById('uploadForm'));
            
            // Remove error message after 5 seconds
            setTimeout(() => errorMessage.remove(), 5000);
        }

        function removeSelectedFile() {
            const fileInput = document.getElementById('fileInput');
            fileInput.value = ''; // Clear the file input
            document.getElementById('uploadPlaceholder').style.display = 'flex';
            document.getElementById('uploadPreview').style.display = 'none';
        }

        function loadDocuments(categoryId = '', search = '') {
            fetch(`api/document_operations.php?action=list&category_id=${categoryId}&search=${search}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const grid = document.getElementById('documentsGrid');
                        if (data.documents.length === 0) {
                            grid.innerHTML = '<div class="no-documents">No documents found</div>';
                        } else {
                            grid.innerHTML = data.documents.map(doc => `
                                <div class="document-card" data-id="${doc.id}" onclick="previewDocument(${doc.id}, '${doc.name}', '${doc.file_path}', '${doc.file_type}')">
                                    <div class="doc-icon">
                                        <i class='bx ${getFileIcon(doc.file_type)}'></i>
                                    </div>
                                    <div class="doc-info">
                                        <h4>${doc.name}</h4>
                                        <p>${formatFileSize(doc.file_size)} • ${formatDate(doc.created_at)}</p>
                                        ${doc.patient_name ? `<p class="patient-name">Patient: ${doc.patient_name}</p>` : ''}
                                    </div>
                                    <div class="doc-actions">
                                        <button class="action-btn" title="Download" onclick="event.stopPropagation(); downloadDocument(${doc.id})">
                                            <i class='bx bx-download'></i>
                                        </button>
                                        <button class="action-btn" title="Delete" onclick="event.stopPropagation(); deleteDocument(${doc.id})">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </div>
                                </div>
                            `).join('');
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function deleteDocument(id) {
            if (confirm('Are you sure you want to delete this document?')) {
                fetch('api/document_operations.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Get current category and search
                        const activeCategory = document.querySelector('.folder-list li.active').dataset.category;
                        const searchTerm = document.getElementById('searchInput').value;
                        
                        // Reload documents and refresh stats
                        loadDocuments(activeCategory, searchTerm);
                        refreshStats();
                    } else {
                        alert('Delete failed. Please try again.');
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        function downloadDocument(id) {
            window.location.href = `api/document_operations.php?action=download&id=${id}`;
        }

        function openUploadModal() {
            document.getElementById('uploadModal').style.display = 'block';
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').style.display = 'none';
            document.getElementById('uploadForm').reset();
        }

        // New Category Modal Functions
        function openNewCategoryModal() {
            document.getElementById('newCategoryModal').style.display = 'block';
        }

        function closeNewCategoryModal() {
            document.getElementById('newCategoryModal').style.display = 'none';
            document.getElementById('newCategoryForm').reset();
        }

        // Helper functions
        function getFileIcon(fileType) {
            if (fileType.includes('pdf')) return 'bxs-file-pdf';
            if (fileType.includes('image')) return 'bxs-file-image';
            if (fileType.includes('word')) return 'bxs-file-doc';
            if (fileType.includes('excel') || fileType.includes('sheet') || fileType.includes('csv')) return 'bxs-file-spreadsheet';
            return 'bxs-file';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const uploadModal = document.getElementById('uploadModal');
            const newCategoryModal = document.getElementById('newCategoryModal');
            const previewModal = document.getElementById('previewModal');
            
            if (event.target === uploadModal) {
                uploadModal.style.display = 'none';
            }
            if (event.target === newCategoryModal) {
                newCategoryModal.style.display = 'none';
            }
            if (event.target === previewModal) {
                closePreviewModal();
            }
        }

        function previewDocument(id, name, filePath, fileType) {
            const modal = document.getElementById('previewModal');
            const container = document.getElementById('previewContainer');
            const title = document.getElementById('previewTitle');
            
            // Set the title
            title.textContent = name;
            
            // Clear previous content
            container.innerHTML = '';
            
            // Create preview based on file type
            if (fileType.includes('image')) {
                // Image preview
                const img = document.createElement('img');
                img.src = filePath;
                img.alt = name;
                container.appendChild(img);
            } else if (fileType.includes('pdf')) {
                // PDF preview
                const iframe = document.createElement('iframe');
                iframe.src = filePath;
                container.appendChild(iframe);
            } else {
                // Unsupported file type
                container.innerHTML = `
                    <div class="unsupported-file">
                        <i class='bx bx-file'></i>
                        <h3>Preview not available</h3>
                        <p>This file type cannot be previewed. Please download the file to view it.</p>
                        <button class="btn-primary" onclick="downloadCurrentDocument()">
                            <i class='bx bx-download'></i> Download File
                        </button>
                    </div>
                `;
            }
            
            // Store current document info for download
            modal.dataset.documentId = id;
            
            // Show the modal
            modal.style.display = 'block';
        }

        function closePreviewModal() {
            document.getElementById('previewModal').style.display = 'none';
        }

        function downloadCurrentDocument() {
            const modal = document.getElementById('previewModal');
            const documentId = modal.dataset.documentId;
            if (documentId) {
                downloadDocument(documentId);
            }
        }
    </script>

    <?php
    // Helper functions
    function formatFileSize($bytes) {
        if ($bytes === 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    function formatDate($dateString) {
        return date('M d, Y H:i', strtotime($dateString));
    }

    function getFileIcon($fileType) {
        if (strpos($fileType, 'pdf') !== false) return 'bxs-file-pdf';
        if (strpos($fileType, 'image') !== false) return 'bxs-file-image';
        if (strpos($fileType, 'word') !== false) return 'bxs-file-doc';
        if (strpos($fileType, 'excel') !== false || strpos($fileType, 'sheet') !== false || strpos($fileType, 'csv') !== false) return 'bxs-file-spreadsheet';
        return 'bxs-file';
    }
    ?>
</body>
</html> 