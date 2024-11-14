<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get category ID from URL
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 1;

// Check if category is admin-only
$sql = "SELECT admin_only FROM categories WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

// If category is admin-only and user is not admin, redirect
if ($category['admin_only'] && !$_SESSION['is_admin']) {
    $_SESSION['error'] = "Only administrators can create topics in this category.";
    header("Location: index.php");
    exit();
}

// Get categories for dropdown
$sql = "SELECT * FROM categories";
$categories = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>UPNM Forum - Create New Topic</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .create-topic-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group input[type="text"]:focus,
        .form-group select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .submit-button {
            background-color: #4CAF50;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #45a049;
        }

        .cancel-button {
            background-color: #e74c3c;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
            transition: background-color 0.3s;
        }

        .cancel-button:hover {
            background-color: #c0392b;
        }

        .button-group {
            margin-top: 2rem;
            text-align: right;
        }

        .content-editor {
            width: 100%;
            min-height: 300px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
        }

        .content-editor:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .editor-toolbar {
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .editor-toolbar button {
            padding: 5px 10px;
            margin-right: 5px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 3px;
            cursor: pointer;
        }

        .editor-toolbar button:hover {
            background: #e9ecef;
        }

        .editor-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        #editor {
            height: 300px;
            font-size: 16px;
        }

        .ql-toolbar {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            background: #f8f9fa;
            border-bottom: 1px solid #e2e8f0;
        }

        .ql-container {
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            font-size: 16px;
            border: 1px solid #e2e8f0;
        }

        .form-group label {
            font-size: 16px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .topic-title {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: border-color 0.3s ease;
        }

        .topic-title:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .submit-button, .cancel-button {
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .submit-button {
            background: #4CAF50;
            color: white;
            border: none;
        }

        .submit-button:hover {
            background: #45a049;
            transform: translateY(-1px);
        }

        .cancel-button {
            background: #e53e3e;
            color: white;
            border: none;
            text-decoration: none;
        }

        .cancel-button:hover {
            background: #c53030;
            transform: translateY(-1px);
        }

        .word-count {
            color: #718096;
            font-size: 14px;
            text-align: right;
            margin-top: 8px;
        }

        .category-select {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 20px;
            background-color: white;
            cursor: pointer;
        }

        .category-select:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="create-topic-container">
            <h2><i class="fas fa-plus-circle"></i> Create New Topic</h2>
            
            <form action="process_topic.php" method="POST" id="topicForm">
                <div class="form-group">
                    <label for="category">Category</label>
                    <select name="category_id" id="category" class="category-select" required>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo ($category['category_id'] == $category_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">Topic Title</label>
                    <input type="text" name="title" id="title" class="topic-title" required 
                           placeholder="Enter a descriptive title for your topic"
                           maxlength="100">
                    <div class="word-count">
                        <span id="titleCount">0</span>/100 characters
                    </div>
                </div>

                <div class="form-group">
                    <label for="editor">Content</label>
                    <div class="editor-container">
                        <div id="editor"></div>
                    </div>
                    <input type="hidden" name="content" id="hiddenContent">
                    <div class="word-count">
                        <span id="wordCount">0</span> words
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="submit-button">
                        <i class="fas fa-paper-plane"></i>
                        Publish Topic
                    </button>
                    <a href="category.php?id=<?php echo $category_id; ?>" class="cancel-button">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> UPNM Forum. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        // Initialize Quill editor
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    ['link', 'image'],
                    ['clean']
                ]
            },
            placeholder: 'Write your topic content here...'
        });

        // Update hidden input with content before form submission
        document.getElementById('topicForm').onsubmit = function() {
            var content = document.getElementById('hiddenContent');
            content.value = quill.root.innerHTML;
            return true;
        };

        // Title character counter
        document.getElementById('title').addEventListener('input', function() {
            document.getElementById('titleCount').textContent = this.value.length;
        });

        // Word counter for Quill editor
        quill.on('text-change', function() {
            var text = quill.getText();
            var words = text.trim().split(/\s+/).length;
            if (text.trim().length === 0) words = 0;
            document.getElementById('wordCount').textContent = words;
        });
    </script>
</body>
</html> 