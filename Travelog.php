<?php
session_start();
include 'dbConnect.php';

// Determine user ID and username
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Anonymous';

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_comment') {
    $commentText = trim($_POST['comment_text'] ?? '');
    if (empty($commentText)) {
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, username, comment_text) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $username, $commentText]);
        echo json_encode(['success' => true, 'message' => 'Comment posted successfully!']);
    } catch (PDOException $e) {
        error_log("Error posting comment: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error posting comment. Please try again.']);
    }
    exit;
}

// Handle fetching comments
if (isset($_GET['action']) && $_GET['action'] === 'fetch_comments') {
    try {
        $stmt = $pdo->prepare("SELECT username, comment_text, created_at FROM comments ORDER BY created_at DESC");
        $stmt->execute();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'comments' => $comments]);
    } catch (PDOException $e) {
        error_log("Error fetching comments: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching comments.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Travelogs</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet" />
  <style>
  body, h1, h2, h3, p, a, ul, li {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

 
  body {
    font-family: 'Poppins', Verdana, Arial, Helvetica, sans-serif;
    background: #303030;
    color: #303030;
  }


  .back-button-container {
    position: fixed; 
    bottom: 15px;
    right: 15px;
    z-index: 1000; 
  }

 
  .back-button {
    background-color: #613604; 
    color: #fff; 
    padding: 8px 16px; 
    border: none; 
    cursor: pointer; 
    font-size: 1rem; 
    border-radius: 5px; 
    transition: background-color 0.3s ease; 
    display: inline-block; 
    text-decoration: none; 
    user-select: none;
  }

  
  .back-button:hover {
    background-color: #aa6413; 
  }

 
  header {
    background-color: #aa6413;
    padding: 15px 20px 15px 0px; 
    display: flex; 
    align-items: center; 
    gap: 15px; 
    color: white; 
    font-size: 2.4rem; 
    font-weight: 600; 
    user-select: none; 
    letter-spacing: 1.2px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5); 
  }

 
  header a {
    display: flex; 
    align-items: center; 
    color: white;
    text-decoration: none; 
  }

 
  header a:hover,
  header a:focus,
  header a:active {
    text-decoration: none; 
  }

  
  header img {
    height: 60px; 
    width: 80px; 
    transform: scale(1.5); 
    transform-origin: left center; 
    display: block; 
    margin-right: 15px; 
  }

  
  nav {
    background-color: #613604; 
    display: flex; 
    justify-content: center; 
    padding: 10px; 
    margin-bottom: 40px; 
  }

  
  nav a {
    color: #fff; 
    text-decoration: none; 
    font-size: 18px; 
    padding: 10px 20px; 
    border-radius: 8px; 
    margin: 0 10px; 
    transition: background-color 0.3s ease, transform 0.2s ease; 
  }

 
  nav a:hover,
  nav a.active {
    background-color: #aa6413; 
    transform: scale(1.05); 
  }

  
  .container {
    max-width: 1020px; 
    margin: 0 auto; 
    padding: 0 15px; 
  }

 
  .main {
    display: flex; 
    margin-top: 20px;
  }

  
  .sidebar-left, .sidebar-right {
    flex: 1 1 200px; 
    background-color: #000; 
    padding: 30px; 
  }

  
  .sidebar-left img {
    width: 105%; 
    display: block; 
  }

  
  .sidebar-right {
    background: #993300; 
    min-height: 650px; 
  }

 
  .main > .sidebar-left {
    margin-right: 20px; 
  }

  .main > .sidebar-right {
    margin-left: 20px; 
  }

 
  .content {
    flex: 2 1 500px; 
    background-color: #ffcc66;
    color: #000;
    padding: 20px; 
    border-radius: 5px;
  }


  .content h2 {
    font-size: 1.4rem; 
    margin-bottom: 10px;
    border-bottom: 2px solid #000; 
    padding-bottom: 5px; 
  }


  .travelog-section {
    margin-bottom: 40px; 
  }

  
  .travelog-title {
    color: #993300; 
    font-weight: bold; 
    font-size: 1.2rem; 
    margin-bottom: 8px; 
    display: flex; 
    align-items: center; 
    gap: 10px;
  }

 
  .travelog-title img {
    width: 32px;
    height: 32px; 
  }

  
  .travelog-text {
    font-size: 0.9rem; 
    font-family: Verdana, Arial, Helvetica, sans-serif; 
  }

 
  .travelog-row {
    display: flex; 
    justify-content: center; 
    gap: 10px;
    margin-bottom: 20px;
  }

  
  .travelog-row img {
    width: 131px;
    height: 77px; 
    object-fit: cover; 
  }

 
  @media (max-width: 768px) {
    .main {
      flex-direction: column; 
    }
    .sidebar-left, .sidebar-right {
      flex: none; 
      width: 100%;
      margin-bottom: 20px; 
    }
    .content {
      margin: 0;
    }
    .travelog-row {
      flex-wrap: wrap; 
      gap: 15px; 
    }
    .travelog-row img {
      width: 100%; 
      height: auto; 
    }
  }

  .comment-section {
    margin-top: 40px; 
    padding: 20px; 
    background-color: #f7f7f7; 
    border-radius: 8px; 
    border: 1px solid #ddd; 
  }

  .comment-section h3 {
    font-size: 1.5rem; 
    color: #303030; 
    margin-bottom: 20px;
    text-align: center; 
  }

  .comment-form {
    display: flex; 
    flex-direction: column; 
    gap: 10px; 
    margin-bottom: 20px; 
  }

  .comment-form textarea {
    width: 100%; 
    padding: 10px; 
    border: 1px solid #ccc; 
    border-radius: 5px; 
    font-family: Arial, sans-serif; 
    font-size: 1rem; 
    resize: vertical; 
    min-height: 80px; 
  }

  .comment-form button {
    background-color: #aa6413; 
    color: white; 
    padding: 10px 15px; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer; 
    font-size: 1rem; 
    transition: background-color 0.3s ease; 
  }

  .comment-form button:hover {
    background-color: #613604; 
  }

  .comments-display {
    border-top: 1px solid #eee; 
    padding-top: 20px; 
  }

  .comment-item {
    background-color: #fff; 
    border: 1px solid #e0e0e0; 
    border-radius: 8px; 
    padding: 15px; 
    margin-bottom: 15px; 
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }

  .comment-meta {
    display: flex;
    justify-content: space-between; 
    font-size: 0.9rem; 
    color: #666; 
    margin-bottom: 10px; 
  }

  .comment-author {
    font-weight: bold; 
    color: #aa6413;
  }

  .comment-date {
    font-style: italic; 
  }

  .comment-text-content {
    font-size: 1rem; 
    color: #333;
    white-space: pre-wrap; 
    word-wrap: break-word; 
  }
  </style>
</head>
<body>
  <div class="back-button-container">
    <a href="CompassHome.html" class="back-button">← Back</a>
  </div>
  <header>
    <a href="CompassHome.html" title="Go to Compass Home">
      <img src="Pictures/logo.svg" alt="Logo" />
      <span>Compass Adventures</span>
    </a>
  </header>
  <nav>
    <a href="TripPlanner.php" id="menu-tripplanner">Trip Planner</a>
    <a href="Destinations.html" id="menu-destinations">Destinations</a>
    <a href="Travelog.php" id="menu-travelogs" class="active">Travel Logs</a>
  </nav>

  <div class="container">
    <div class="main">
      <aside class="sidebar-left">
        <img src="Pictures/bookImage.gif" alt="Book Image" />
      </aside>
      <section class="content">
        <h2>Travel Logs</h2>
        <div class="travelog-row">
          <img src="Pictures/kayaking.jpg" alt="Kayaking" />
          <img src="Pictures/climber.jpg" alt="Climber" />
          <img src="Pictures/bike.jpg" alt="Bike" />
        </div>
        <div class="travelog-section">
          <div class="travelog-title">
            <img src="Pictures/iconKayak.gif" alt="Kayak Icon" />
            Conquering the rapids of the Rutan Islands
          </div>
          <p class="travelog-text">Join us as we paddle through the pristine and powerful waters of the Rutan Islands...</p>
        </div>
        <div class="comment-section">
          <h3>Users Log/Blog</h3>
          <form class="comment-form" id="commentForm">
            <textarea id="commentText" name="comment_text" placeholder="Leave a comment..."></textarea>
            <button type="submit">Post Comment</button>
          </form>
          <div id="commentsContainer" class="comments-display">
          </div>
        </div>
      </section>
      <aside class="sidebar-right">
      </aside>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const commentForm = document.getElementById('commentForm');
      const commentText = document.getElementById('commentText');
      const commentsContainer = document.getElementById('commentsContainer');

      // Fetch comments
      function loadComments() {
        fetch('Travelog.php?action=fetch_comments')
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              commentsContainer.innerHTML = '';
              data.comments.forEach(comment => {
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment-item';
                commentDiv.innerHTML = `
                  <div class="comment-meta">
                    <span class="comment-author">${comment.username}</span>
                    <span class="comment-date">${new Date(comment.created_at).toLocaleString()}</span>
                  </div>
                  <div class="comment-text-content">${comment.comment_text}</div>
                `;
                commentsContainer.appendChild(commentDiv);
              });
            } else {
              commentsContainer.innerHTML = `<p>${data.message}</p>`;
            }
          });
      }

      commentForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = commentText.value.trim();
        if (!text) {
          alert('Please write a comment.');
          return;
        }

        fetch('Travelog.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `action=submit_comment&comment_text=${encodeURIComponent(text)}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            commentText.value = '';
            loadComments();
          } else {
            alert(data.message);
          }
        });
      });

      // Load comments on page load
      loadComments();
    });
  </script>
</body>
</html>
