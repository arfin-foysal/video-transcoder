<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HLS Player with Plyr.js and API Integration</title>
    <!-- Plyr.js CSS -->
    <link rel="stylesheet" href="https://cdn.plyr.io/3.6.8/plyr.css" />
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #1f1f1f;
            color: #fff;
        }
        #form-container {
            margin-bottom: 20px;
            text-align: center;
        }
        input[type="text"] {
            padding: 10px;
            border: none;
            border-radius: 5px;
            margin-right: 10px;
            font-size: 16px;
            background-color: #333;
            color: #fff;
            width: 200px;
        }
        input[type="text"]::placeholder {
            color: #999;
        }
        input[type="submit"] {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        #player {
            width: 50%; /* Half the screen width */
            height: 50%; /* Half the screen height */
            position: relative;
            background-color: #000; /* Optional: Ensures the player background is black */
        }
        .resolution-control {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.5);
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            z-index: 11;
        }
        .resolution-menu {
            display: none;
            position: absolute;
            background: #333;
            border-radius: 5px;
            padding: 5px;
            list-style: none;
            top: 30px;
            right: 10px;
            z-index: 11;
            width: 100px;
            font-size: 14px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        .resolution-menu.show {
            display: block;
        }
        .resolution-menu li {
            cursor: pointer;
            padding: 5px;
            transition: background-color 0.3s ease;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .resolution-menu li:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <div id="form-container">
        <h1>Transcoded Video</h1>
        <form id="video-form">
            <input type="text" id="video-id" name="video-id" placeholder="Enter Video ID" required>
            <input type="submit" value="Load Video">
        </form>
    </div>
    <div id="player">
        <video id="my-video" class="plyr__video-embed" controls></video>
        <div class="resolution-control">Quality</div>
        <ul class="resolution-menu"></ul>
    </div>

    <!-- Plyr.js JavaScript -->
    <script src="https://cdn.plyr.io/3.6.8/plyr.polyfilled.js"></script>
    <!-- hls.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const videoElement = document.getElementById('my-video');
            const player = new Plyr(videoElement);
            const resolutionControl = document.querySelector('.resolution-control');
            const resolutionMenu = document.querySelector('.resolution-menu');
            const form = document.getElementById('video-form');

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const videoId = document.getElementById('video-id').value;
                fetchVideoById(videoId);
            });

            function fetchVideoById(videoId) {
                fetch(`http://localhost:8000/api/video/${videoId}`)
                    .then(response => response.json())
                    .then(data => {
                        const videoSrc = data.data.transcoded_url;
                        loadVideo(videoSrc);
                    })
                    .catch(error => {
                        console.error('Error fetching video data:', error);
                    });
            }

            function loadVideo(videoSrc) {
                if (Hls.isSupported()) {
                    const hls = new Hls();
                    hls.loadSource(videoSrc);
                    hls.attachMedia(videoElement);

                    hls.on(Hls.Events.MANIFEST_PARSED, function (event, data) {
                        const qualities = hls.levels.map((level, index) => ({
                            label: level.height + 'p',
                            index: index
                        }));

                        createResolutionMenu(qualities, hls);
                    });

                    videoElement.addEventListener('play', function () {
                        hls.startLoad();
                    });
                } else if (videoElement.canPlayType('application/vnd.apple.mpegurl')) {
                    videoElement.src = videoSrc;
                    videoElement.addEventListener('loadedmetadata', function () {
                        const qualities = Array.from(videoElement.videoTracks).map((track, index) => ({
                            label: track.height + 'p',
                            index: index
                        }));

                        createResolutionMenu(qualities, null);
                    });
                }
            }

            function createResolutionMenu(qualities, hls) {
                resolutionMenu.innerHTML = ''; // Clear existing menu items
                qualities.forEach(function (quality) {
                    const menuItem = document.createElement('li');
                    menuItem.textContent = quality.label;
                    menuItem.onclick = function () {
                        if (hls) {
                            hls.currentLevel = quality.index;
                        } else {
                            videoElement.videoTracks[quality.index].enabled = true;
                        }
                        player.play();
                    };
                    resolutionMenu.appendChild(menuItem);
                });

                resolutionControl.addEventListener('click', function () {
                    resolutionMenu.classList.toggle('show');
                });

                document.addEventListener('click', function (event) {
                    if (!resolutionControl.contains(event.target)) {
                        resolutionMenu.classList.remove('show');
                    }
                });
            }
            player.on('ready', function() {
                if (videoElement.requestFullscreen) {
                    videoElement.requestFullscreen();
                } else if (videoElement.mozRequestFullScreen) { // Firefox
                    videoElement.mozRequestFullScreen();
                } else if (videoElement.webkitRequestFullscreen) { // Chrome, Safari and Opera
                    videoElement.webkitRequestFullscreen();
                } else if (videoElement.msRequestFullscreen) { // IE/Edge
                    videoElement.msRequestFullscreen();
                }
            });
        });
    </script>
</body>
</html>
<?php /**PATH /home/foysal/Development/video-transcoder/resources/views/hls.blade.php ENDPATH**/ ?>