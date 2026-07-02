import os
from PIL import Image
from collections import deque

def make_transparent(file_path):
    print(f"Processing {file_path}...")
    img = Image.open(file_path).convert("RGBA")
    w, h = img.size
    pixels = img.load()
    
    # We will do a BFS from the borders to find all background pixels
    visited = [[False] * h for _ in range(w)]
    queue = deque()
    
    # Identify background threshold: light color with low chroma
    def is_bg_color(r, g, b):
        # High brightness and low saturation/difference
        max_c = max(r, g, b)
        min_c = min(r, g, b)
        diff = max_c - min_c
        return (min_c > 160 and diff < 60) or (min_c > 220)
    
    # Add all matching border pixels to queue
    for x in range(w):
        for y in [0, h - 1]:
            r, g, b, a = pixels[x, y]
            if is_bg_color(r, g, b):
                visited[x][y] = True
                queue.append((x, y))
                
    for y in range(h):
        for x in [0, w - 1]:
            if not visited[x][y]:
                r, g, b, a = pixels[x, y]
                if is_bg_color(r, g, b):
                    visited[x][y] = True
                    queue.append((x, y))
                    
    bg_count = 0
    while queue:
        cx, cy = queue.popleft()
        # Set alpha to 0 for background
        pixels[cx, cy] = (255, 255, 255, 0)
        bg_count += 1
        
        # Check 4 neighbors
        for dx, dy in [(-1, 0), (1, 0), (0, -1), (0, 1)]:
            nx, ny = cx + dx, cy + dy
            if 0 <= nx < w and 0 <= ny < h and not visited[nx][ny]:
                r, g, b, a = pixels[nx, ny]
                if is_bg_color(r, g, b):
                    visited[nx][ny] = True
                    queue.append((nx, ny))
                    
    # Anti-aliasing cleanup: for pixels adjacent to transparent background that are light grey/cream
    for x in range(w):
        for y in range(h):
            r, g, b, a = pixels[x, y]
            if a > 0:
                max_c = max(r, g, b)
                min_c = min(r, g, b)
                diff = max_c - min_c
                # If it's a light transition pixel
                if min_c > 180 and diff < 50:
                    # Smooth fade
                    new_a = int(255 * (240 - min_c) / 60.0)
                    new_a = max(0, min(255, new_a))
                    if new_a < 10:
                        pixels[x, y] = (255, 255, 255, 0)
                    else:
                        pixels[x, y] = (r, g, b, new_a)

    img.save(file_path, "PNG")
    print(f"-> Done {file_path}. Removed {bg_count} background pixels.")

icons_dir = "public/images/icons"
for filename in os.listdir(icons_dir):
    if filename.endswith(".png"):
        make_transparent(os.path.join(icons_dir, filename))
