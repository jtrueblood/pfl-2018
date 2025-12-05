#!/usr/bin/env python3
"""
Script to search Google Images for player photos using Google Custom Search API.
Returns only webp and jpeg format images.

Requirements:
- API Key from Google Cloud Console
- Custom Search Engine ID (CX)

Setup:
1. Go to https://console.cloud.google.com/apis/credentials
2. Create an API key
3. Go to https://programmablesearchengine.google.com/
4. Create a Custom Search Engine and enable "Image search"
5. Get your Search Engine ID (CX)
6. Set environment variables: GOOGLE_API_KEY and GOOGLE_CX_ID
"""

import requests
import os
import webbrowser
import tempfile
from urllib.parse import urlencode, urlparse, parse_qs
from http.server import HTTPServer, BaseHTTPRequestHandler
import threading
import json
from pathlib import Path
import subprocess
import mysql.connector


# ============================================================================
# CONFIGURATION - Set your API credentials here
# ============================================================================
API_KEY = "AIzaSyBUIM6jF_MU_96SYkzAlsD05oiu00l6K4s"  # Your Google API key
CX_ID = "b10d46301d4e44086"     # Your Custom Search Engine ID
WP_PATH = "/Users/jamietrueblood/Local Sites/posse-football-league/app/public"
WP_UPLOADS_DIR = "/Users/jamietrueblood/Local Sites/posse-football-league/app/public/wp-content/uploads"
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

# Database configuration
DB_HOST = "localhost"
DB_USER = "root"
DB_PASSWORD = "root"
DB_NAME = "local"
# ============================================================================


def lookup_player_id(player_name):
    """
    Look up player ID from wp_players table by name.
    
    Args:
        player_name: Full name of the player (e.g., "Troy Aikman")
    
    Returns:
        Player ID if found, None otherwise
    """
    try:
        # Parse the player name
        name_parts = player_name.strip().split()
        if len(name_parts) < 2:
            return None
        
        first_name = name_parts[0]
        last_name = ' '.join(name_parts[1:])  # Handle multi-part last names
        
        # Connect to database
        conn = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME,
            unix_socket=MYSQL_SOCKET
        )
        
        cursor = conn.cursor()
        
        # Query for player
        query = "SELECT p_id FROM wp_players WHERE playerFirst = %s AND playerLast = %s LIMIT 1"
        cursor.execute(query, (first_name, last_name))
        
        result = cursor.fetchone()
        
        cursor.close()
        conn.close()
        
        if result:
            return result[0]
        return None
        
    except Exception as e:
        print(f"Error looking up player: {e}")
        return None


def get_google_image_urls(player_name, max_results=50, api_key=None, cx_id=None):
    """
    Search Google Images for a player name using Custom Search API.
    
    Args:
        player_name: Name of the player to search for
        max_results: Maximum number of image URLs to return (default: 10)
        api_key: Google API key (if not provided, reads from GOOGLE_API_KEY env var)
        cx_id: Custom Search Engine ID (if not provided, reads from GOOGLE_CX_ID env var)
    
    Returns:
        List of image URLs (webp and jpeg only)
    """
    # Get API credentials (priority: parameter > config > environment variable)
    api_key = api_key or API_KEY or os.environ.get('GOOGLE_API_KEY')
    cx_id = cx_id or CX_ID or os.environ.get('GOOGLE_CX_ID')
    
    if not api_key or not cx_id:
        print("Error: Missing API credentials.")
        print("Set GOOGLE_API_KEY and GOOGLE_CX_ID environment variables.")
        return []
    
    # Google Custom Search API endpoint
    base_url = "https://www.googleapis.com/customsearch/v1"
    
    image_urls = []
    
    # API returns max 10 results per request, so we may need multiple requests
    num_requests = (max_results + 9) // 10  # Ceiling division
    
    for page in range(num_requests):
        start_index = page * 10 + 1
        results_needed = min(10, max_results - len(image_urls))
        
        # Build API request parameters
        params = {
            'key': api_key,
            'cx': cx_id,
            'q': f"{player_name} game image nfl",
            'searchType': 'image',
            'fileType': 'jpg,webp',  # Filter for jpg and webp
            'num': results_needed,
            'start': start_index
        }
        
        try:
            response = requests.get(base_url, params=params, timeout=10)
            response.raise_for_status()
            
            data = response.json()
            
            # Extract image URLs from results
            if 'items' in data:
                for item in data['items']:
                    if 'link' in item:
                        image_urls.append(item['link'])
                        if len(image_urls) >= max_results:
                            break
            else:
                # No more results available
                break
                
        except requests.RequestException as e:
            print(f"Error fetching search results: {e}")
            if hasattr(e, 'response') and e.response is not None:
                try:
                    error_data = e.response.json()
                    if 'error' in error_data:
                        print(f"API Error: {error_data['error'].get('message', 'Unknown error')}")
                except:
                    pass
            break
        
        if len(image_urls) >= max_results:
            break
    
    return image_urls[:max_results]


def download_image(image_url, player_id, wp_path=WP_PATH):
    """
    Download an image and import it to WordPress media library using PHP script.
    
    Args:
        image_url: URL of the image to download
        player_id: Player ID to use as filename
        wp_path: WordPress installation path
    
    Returns:
        dict with success status and message
    """
    try:
        # Path to the PHP helper script
        php_script = os.path.join(os.path.dirname(__file__), 'wp_media_upload.php')
        
        # Run the PHP script with arguments
        result = subprocess.run(
            [
                'php',
                php_script,
                f'--url={image_url}',
                f'--title={player_id}',
                f'--filename={player_id}',
                f'--socket={MYSQL_SOCKET}'
            ],
            capture_output=True,
            text=True,
            timeout=30
        )
        
        # Parse the JSON response
        if result.returncode == 0:
            try:
                # Extract JSON from output (may have warnings before it)
                output = result.stdout
                # Find the JSON object in the output
                json_start = output.find('{')
                if json_start >= 0:
                    json_str = output[json_start:]
                    # Try to find the end of the JSON
                    json_end = json_str.find('}') + 1
                    if json_end > 0:
                        json_str = json_str[:json_end]
                    response_data = json.loads(json_str)
                else:
                    response_data = json.loads(output)
                
                if response_data.get('success'):
                    return {
                        'success': True,
                        'message': f'Image uploaded to WordPress media library',
                        'attachment_id': response_data.get('attachment_id'),
                        'filename': response_data.get('filename'),
                        'url': response_data.get('attachment_url')
                    }
                else:
                    return {
                        'success': False,
                        'message': response_data.get('message', 'Unknown error')
                    }
            except (json.JSONDecodeError, ValueError) as e:
                return {
                    'success': False,
                    'message': f'Invalid response from PHP script. Error: {str(e)}'
                }
        else:
            # Try to parse error from stdout
            try:
                error_data = json.loads(result.stdout)
                message = error_data.get('message', result.stderr or 'Unknown error')
            except:
                message = result.stderr or result.stdout or 'Unknown error'
            
            return {
                'success': False,
                'message': f'PHP script failed: {message}'
            }
    
    except subprocess.TimeoutExpired:
        return {
            'success': False,
            'message': 'Upload timed out after 30 seconds'
        }
    except Exception as e:
        return {
            'success': False,
            'message': f'Error uploading image: {str(e)}'
        }


class ImageDownloadHandler(BaseHTTPRequestHandler):
    """HTTP request handler for image downloads."""
    
    player_ids = {}  # Store player IDs by session
    
    def do_POST(self):
        """Handle POST requests to download images."""
        if self.path == '/download':
            # Read request body
            content_length = int(self.headers['Content-Length'])
            post_data = self.rfile.read(content_length)
            data = json.loads(post_data.decode('utf-8'))
            
            image_url = data.get('image_url')
            player_id = data.get('player_id')
            
            if image_url and player_id:
                result = download_image(image_url, player_id)
                
                # Send response
                self.send_response(200)
                self.send_header('Content-type', 'application/json')
                self.send_header('Access-Control-Allow-Origin', '*')
                self.end_headers()
                self.wfile.write(json.dumps(result).encode())
            else:
                self.send_error(400, 'Missing image_url or player_id')
        else:
            self.send_error(404)
    
    def do_OPTIONS(self):
        """Handle OPTIONS requests for CORS."""
        self.send_response(200)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')
        self.end_headers()
    
    def log_message(self, format, *args):
        """Suppress default logging."""
        pass


def start_server(port=8765):
    """Start the HTTP server in a background thread."""
    server = HTTPServer(('localhost', port), ImageDownloadHandler)
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    return server


def display_thumbnails(image_urls, player_name, player_id):
    """
    Create an HTML page with image thumbnails and open it in the browser.
    
    Args:
        image_urls: List of image URLs to display
        player_name: Name of the player
        player_id: ID of the player
    """
    html_content = f"""<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Image Results - {player_name}</title>
    <style>
        body {{
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }}
        h1 {{
            color: #333;
            text-align: center;
        }}
        .info {{
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }}
        .gallery {{
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 20px 0;
        }}
.image-card {{
            background: white;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            cursor: pointer;
            position: relative;
        }}
        .image-card:hover {{
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }}
        .image-card.downloading {{
            opacity: 0.6;
            pointer-events: none;
        }}
        .image-card.success {{
            border: 3px solid #4caf50;
        }}
        .image-container {{
            width: 100%;
            height: 250px;
            overflow: hidden;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            position: relative;
        }}
        .image-container img {{
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }}
        .status-overlay {{
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            display: none;
        }}
        .downloading .status-overlay,
        .success .status-overlay {{
            display: block;
        }}
        .image-number {{
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 8px;
            font-weight: bold;
        }}
        .image-url {{
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 5px;
            word-break: break-all;
        }}
        .image-url a {{
            color: #4285f4;
            text-decoration: none;
        }}
        .image-url a:hover {{
            text-decoration: underline;
        }}
        .error-img {{
            color: #999;
            text-align: center;
            padding: 20px;
        }}
    </style>
</head>
<body>
    <h1>Image Search Results</h1>
    <div class="info">
        <p><strong>Player:</strong> {player_name}</p>
        <p><strong>Player ID:</strong> {player_id}</p>
        <p><strong>Results:</strong> {len(image_urls)} images found</p>
    </div>
    <div class="gallery">
"""
    
    for i, url in enumerate(image_urls, 1):
        # Escape URL for use in JavaScript
        escaped_url = url.replace('"', '&quot;').replace("'", "&#39;")
        html_content += f"""        <div class="image-card" id="card-{i}" onclick="downloadImage('{escaped_url}', {i})">
            <div class="image-container">
                <img src="{url}" alt="Image {i}" onerror="document.getElementById('card-{i}').style.display='none';">
                <div class="status-overlay" id="status-{i}">Downloading...</div>
            </div>
            <div class="image-number">Image {i}</div>
            <div class="image-url">Click to download</div>
        </div>
"""
    
    html_content += f"""    </div>
    <script>
        const PLAYER_ID = '{player_id}';
        
async function downloadImage(imageUrl, cardNum) {{
            const card = document.getElementById('card-' + cardNum);
            const status = document.getElementById('status-' + cardNum);
            
            // Mark as downloading
            card.classList.add('downloading');
            status.textContent = 'Downloading...';
            
            try {{
                const response = await fetch('http://localhost:8765/download', {{
                    method: 'POST',
                    headers: {{
                        'Content-Type': 'application/json'
                    }},
                    body: JSON.stringify({{
                        image_url: imageUrl,
                        player_id: PLAYER_ID
                    }})
                }});
                
                const result = await response.json();
                
                if (result.success) {{
                    card.classList.remove('downloading');
                    card.classList.add('success');
                    status.textContent = 'Saved!';
                    
                    // Remove success indicator after 3 seconds
                    setTimeout(() => {{
                        card.classList.remove('success');
                    }}, 3000);
                }} else {{
                    card.classList.remove('downloading');
                    status.textContent = 'Error: ' + result.message;
                    alert('Failed to download: ' + result.message);
                }}
            }} catch (error) {{
                card.classList.remove('downloading');
                status.textContent = 'Error!';
                alert('Error downloading image: ' + error.message);
            }}
        }}
    </script>
</body>
</html>"""
    
    # Create a temporary HTML file
    with tempfile.NamedTemporaryFile(mode='w', suffix='.html', delete=False) as f:
        f.write(html_content)
        temp_file = f.name
    
    # Open in default browser
    print(f"\nOpening thumbnail gallery in browser...")
    webbrowser.open('file://' + temp_file)
    print(f"Temporary file created: {temp_file}")
    
    return temp_file


def search_player():
    """Search for a player and display their images."""
    # Get player name
    player_name = input("\nEnter player name (or 'quit' to exit): ").strip()
    
    if player_name.lower() in ['quit', 'exit', 'q']:
        return None
    
    if not player_name:
        print("Error: Player name cannot be empty.")
        return False
    
    # Look up player ID from database
    print("\nLooking up player in database...")
    found_player_id = lookup_player_id(player_name)
    
    # Get player ID (pre-filled if found)
    if found_player_id:
        print(f"Found player ID: {found_player_id}")
        player_id = input(f"Player ID [{found_player_id}]: ").strip()
        if not player_id:
            player_id = found_player_id
    else:
        print("Player not found in database.")
        player_id = input("Enter player ID: ").strip()
    
    if not player_id:
        print("Error: Player ID cannot be empty.")
        return False
    
    print(f"\nSearching for images of: {player_name}")
    print(f"Player ID: {player_id}")
    print("-" * 60)
    
    # Search for images
    image_urls = get_google_image_urls(player_name, max_results=30)
    
    if image_urls:
        print(f"\nFound {len(image_urls)} image URL(s)")
        
        # Display thumbnails in browser
        display_thumbnails(image_urls, player_name, player_id)
        
        # Also print URLs for reference
        print("\nImage URLs:")
        for i, url in enumerate(image_urls, 1):
            print(f"{i}. {url}")
        
        print("\n" + "=" * 60)
        return True
    else:
        print("\nNo images found. This could be due to:")
        print("- Missing or invalid API credentials")
        print("- Network connectivity issues")
        print("- No matching images available")
        print("- API quota exceeded")
        print("\n" + "=" * 60)
        return False


def main():
    """Main function to get user input and display image URLs."""
    print("=" * 60)
    print("Google Images Player Photo Search")
    print("=" * 60)
    
    # Start the HTTP server once
    print("\nStarting download server on http://localhost:8765...")
    server = start_server()
    print("Server started successfully.")
    print(f"Images will be imported to WordPress media library")
    print(f"WordPress installation: {WP_PATH}")
    print("\nYou can search for multiple players. Type 'quit' to exit.")
    print("=" * 60)
    
    try:
        # Keep searching for players
        while True:
            result = search_player()
            if result is None:  # User wants to quit
                break
    except KeyboardInterrupt:
        print("\n\nInterrupted by user.")
    finally:
        print("\nShutting down server...")
        server.shutdown()
        print("Server stopped. Goodbye!")


if __name__ == "__main__":
    main()
