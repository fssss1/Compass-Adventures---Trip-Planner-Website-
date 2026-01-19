<?php
session_start();
echo "Session user ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
require_once 'dbConnect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'] ?? null;

    $city = trim($_POST['city'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $notes = $_POST['notes'] ?? '';

    $activities = $_POST['activities'] ?? [];
    $group_size = $_POST['group_size'] ?? null;
    $experience_level = $_POST['experience_level'] ?? null;

 
    $trip_info_needs = $_POST['trip_info_needs'] ?? []; 

    if (!$user_id || (!$city && !$region) || !$start_date || !$end_date) {
        echo "<script>alert('Please complete all required fields (city/region, and dates).');</script>";
        exit;
    } else {

        $activities_json = json_encode($activities);
        $trip_info_needs_json = json_encode($trip_info_needs);

        $sql = "INSERT INTO trip_planner (user_id, city, region, start_date, end_date, activities, group_size, experience_level, notes, trip_info_needs)
                VALUES (:user_id, :city, :region, :start_date, :end_date, :activities, :group_size, :experience_level, :notes, :trip_info_needs)";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':city' => $city,
                ':region' => $region,
                ':start_date' => $start_date,
                ':end_date' => $end_date,
                ':activities' => $activities_json,
                ':group_size' => $group_size,
                ':experience_level' => $experience_level,
                ':notes' => $notes,
                ':trip_info_needs' => $trip_info_needs_json 
            ]);

            echo "<script>alert('Trip successfully saved!');</script>";
        } catch (PDOException $e) {
            error_log("DB Insert Error: " . $e->getMessage());
            echo "<script>alert('An error occurred while saving your trip. Please check server logs for details.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Trip Planner</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet" />
  <style>
  
     body, h1, h2, h3, p, a, ul, li { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Poppins', Verdana, Arial, Helvetica, sans-serif; background: #303030; color: #303030; font-size: 14px; }
    .back-button-container { position: fixed; bottom: 15px; right: 15px; z-index: 1000; }
    .back-button { background-color: #613604; color: #fff; padding: 8px 16px; border: none; cursor: pointer; font-size: 1rem; border-radius: 5px; transition: background-color 0.3s ease; display: inline-block; text-decoration: none; user-select: none; }
    .back-button:hover { background-color: #aa6413; }
    header { background-color: #aa6413; padding: 15px 20px 15px 0px; display: flex; align-items: center; gap: 15px; color: white; font-size: 2.4rem; font-weight: 600; user-select: none; letter-spacing: 1.2px; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); }
    header a { display: flex; align-items: center; color: white; text-decoration: none; }
    header img { height: 60px; width: 80px; transform: scale(1.5); transform-origin: left center; display: block; margin-right: 15px; }
    nav { background-color: #613604; display: flex; padding: 10px; justify-content: center; margin-bottom: 40px; }
    nav a { color: #fff; text-decoration: none; font-size: 18px; padding: 10px 20px; border-radius: 8px; margin: 0 10px; transition: background-color 0.3s ease, transform 0.2s ease; }
    nav a:hover, nav a.active { background-color: #aa6413; transform: scale(1.05); }
    .container { max-width: 1020px; margin: auto; padding: 0 15px; }
    .main { display: flex; margin-top: 20px; gap: 15px; }
    .map-container { flex: 0 0 400px; border: 3px solid #993300; border-radius: 15px; overflow: hidden; height: 600px; background: white; }
    .map-container iframe { width: 100%; height: 100%; border: none; }
    .content { flex-grow: 1; background: #ffcc66; padding: 50px; box-sizing: border-box; margin: 0; border: 2px solid #993300; border-radius: 15px; margin-bottom: 20px; }
    .content h2 { font-size: 1.4rem; margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 5px; }
    .planner-section { margin-bottom: 30px; font-family: Verdana, Arial, Helvetica, sans-serif; }
    .planner-section label { display: block; margin-bottom: 8px; font-weight: 600; color: #993300; }
    .planner-section input, .planner-section select, .planner-section textarea { padding: 8px; border: 2px solid #993300; border-radius: 6px; font-size: 1rem; margin-bottom: 15px; display: block; }
    .input-group { display: flex; gap: 20px; margin-bottom: 0; }
    .input-group input#group-size, .input-group select#experience-level { width: 250px; }
    .planner-section textarea#notes { width: 100%; height: auto; margin-top: 0; resize: vertical; }
    select#experience-level option:first-child { font-weight: normal; }
    .planner-section button { background-color: #613604; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 1.1rem; transition: background-color 0.3s ease; }
    .planner-section button:hover { background-color: #aa6413; }
    .planner-section h3 { font-size: 1.3rem; margin-bottom: 10px; color: #993300; }
    .planner-section:last-of-type { margin-bottom: 0; padding-bottom: 0; }
    @media (max-width: 768px) { .main { flex-direction: column; } .map-container { width: 100%; height: 400px; margin-bottom: 20px; } .content { margin: 0; } .input-group { flex-direction: column; } .input-group input#group-size, .input-group select#experience-level { width: 100%; } }
    .activity-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; background-color: #ffcc66; padding: 10px; border-radius: 5px; }
    .activity-item { background-color: #ff9c2b; border-radius: 8px; height: 40px; display: flex; align-items: center; color: white; font-size: 0.8rem; padding: 0; }
    .activity-item label { display: flex; align-items: center; gap: 8px; width: 100%; cursor: pointer; padding-left: 12px; }
    .activity-item input[type="checkbox"] { margin: 0; vertical-align: middle; flex-shrink: 0; width: 18px; height: 18px; }
    .date-range { display: flex; align-items: center; gap: 10px; }
    .date-range span { font-weight: 600; font-size: 1rem; user-select: none; }
    .custom-select select { appearance: none; -webkit-appearance: none; -moz-appearance: none; background-color: #fff; border: 2px solid #993300; padding: 8px 12px; font-size: 1rem; border-radius: 6px; width: 250px; font-weight: normal; color: #333; background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M0 2.5l5 5 5-5z' fill='%23613304'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; background-size: 12px; }
    .custom-select { position: relative; display: inline-block; }
    .custom-select select:invalid { color: #999; }
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
    <a href="TripPlanner.php" class="active">Trip Planner</a>
    <a href="Destinations.html">Destinations</a>
    <a href="Travelog.php">Travel Logs</a>
  </nav>

  <div class="container">
    <div class="main">

      <div class="map-container">
        <iframe
          src="https://www.google.com/maps?q=Asia&output=embed"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
      </div>

      <section class="content">
        <form method="POST" action="TripPlanner.php">

          <div class="planner-section">
            <h3>1. Choose Your Destination</h3>
            <label for="city">City or Closest Major City:</label>
            <input type="text" id="city" name="city" placeholder="e.g., Tokyo, New York" required />

            <label for="region">Country or Region:</label>
            <input type="text" id="region" name="region" placeholder="e.g., Japan, USA" required />
          </div>

          <div class="planner-section">
            <h3>2. Information About This Trip</h3>
            <div class="activity-container"> <div class="activity-item"><label><input type="checkbox" name="trip_info_needs[]" value="Transportation" /> Transportation</label></div>
              <div class="activity-item"><label><input type="checkbox" name="trip_info_needs[]" value="Weather" /> Weather</label></div>
              <div class="activity-item"><label><input type="checkbox" name="trip_info_needs[]" value="Political Info" /> Political Info</label></div>
              <div class="activity-item"><label><input type="checkbox" name="trip_info_needs[]" value="Health" /> Health</label></div>
              <div class="activity-item"><label><input type="checkbox" name="trip_info_needs[]" value="Gear" /> Gear</label></div>
              <div class="activity-item"><label><input type="checkbox" name="trip_info_needs[]" value="Activity Specific" /> Activity Specific</label></div>
            </div>
          </div>
          <div class="planner-section">
            <h3>3. Select Activities</h3> <div class="activity-container">
              <div class="activity-item"><label><input type="checkbox" name="activities[]" value="Rock Climbing" /> Rock Climbing</label></div>
              <div class="activity-item"><label><input type="checkbox" name="activities[]" value="Surfing" /> Surfing</label></div>
              <div class="activity-item"><label><input type="checkbox" name="activities[]" value="Hiking" /> Hiking</label></div>
              <div class="activity-item"><label><input type="checkbox" name="activities[]" value="Skiing" /> Skiing</label></div>
              <div class="activity-item"><label><input type="checkbox" name="activities[]" value="Paragliding" /> Paragliding</label></div>
              <div class="activity-item"><label><input type="checkbox" name="activities[]" value="Mountain Biking" /> Mountain Biking</label></div>
              <div class="activity-item"><label><input type="checkbox" name="activities[]" value="Kayaking" /> Kayaking</label></div>
              <div class="activity-item"><label><input type="checkbox" name="activities[]" value="Camping" /> Camping</label></div>
              <div class="activity-item"><label><input type="checkbox" name="activities[]" value="White Water Rafting" /> White Water Rafting</label></div>
            </div>
          </div>

          <div class="planner-section">
            <label for="date-range">Date Range:</label>
            <div class="date-range">
              <input type="date" id="start-date" name="start_date" required />
              <span>To</span>
              <input type="date" id="end-date" name="end_date" required />
            </div>
          </div>

          <div class="planner-section">
            <h3>4. Additional Information</h3> <div class="input-group">
              <div>
                <label for="group-size">Group Size:</label>
                <input type="number" id="group-size" name="group_size" min="1" placeholder="Number of people" />
              </div>

              <div>
                <div class="custom-dropdown-wrapper">
                  <label for="experience-level">Experience Level:</label>
                  <div class="custom-select">
                    <select id="experience-level" name="experience_level">
                      <option value="" disabled selected hidden>Select your experience level</option>
                      <option value="beginner">Beginner</option>
                      <option value="intermediate">Intermediate</option>
                      <option value="advanced">Advanced</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <label for="notes">Notes:</label>
              <textarea id="notes" name="notes" rows="3" placeholder="Enter any special requests or info..."></textarea>
            </div>
          </div>

          <div class="planner-section">
            <button type="submit">Plan My Trip</button>
            <a href="ViewTrips.php" class="back-button" style="margin-left: 15px; vertical-align: middle;">View My Trips</a>
          </div>
        </form>
      </section>
    </div>
  </div>

  <script>
    const cityInput = document.getElementById("city");
    const regionInput = document.getElementById("region");
    const mapFrame = document.querySelector(".map-container iframe");
    const tripButton = document.querySelector("button[type='submit']");

    function updateMap() {
      const city = cityInput.value.trim();
      const region = regionInput.value.trim();

      if (!city && !region) {
        return;
      }

      let query = "";
      if (city && region) {
        query = `${city}, ${region}`;
      } else if (city) {
        query = city;
      } else {
        query = region;
      }

      const encodedQuery = encodeURIComponent(query);


      const newSrc = `https://maps.google.com/maps?q=${encodedQuery}&output=embed`;

      mapFrame.src = newSrc;
    }
    tripButton.addEventListener("click", function () { 
      updateMap();

    });
  </script>

</body>
</html>
