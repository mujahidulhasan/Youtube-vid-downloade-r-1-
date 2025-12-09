<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Video Downloader - ????? ????????</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Noto Sans', 'Hind Siliguri', sans-serif;
        }

        :root {
            --primary-blue: #3EAEFF;
            --secondary-blue: #1abc9c; 
            --text-dark: #333333;
            --text-medium: #666666;
            --background-grey: #f7f7f7;
            --card-border-grey: #dcdcdc;
            --body-bg: var(--background-grey);
            --container-bg: #ffffff;
            --sidebar-bg: #ffffff;
            --sidebar-text: var(--text-dark);
            --sidebar-hover: var(--background-grey);
        }

        body.dark-mode {
            --text-dark: #ecf0f1;
            --text-medium: #bdc3c7;
            --background-grey: #2c3e50;
            --body-bg: #1e272e;
            --container-bg: #2c3e50;
            --card-border-grey: #34495e;
            --sidebar-bg: #2c3e50;
            --sidebar-text: #ecf0f1;
            --sidebar-hover: #34495e;
        }

        body {
            background-color: var(--body-bg);
            color: var(--text-dark);
            display: flex;
            justify-content: center;
            align-items: center; 
            min-height: 100vh; 
            padding: 0; 
            transition: background-color 0.3s;
        }

        .container {
            width: 100%;
            max-width: 450px;
            background-color: var(--container-bg);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: relative;
            z-index: 10;
            min-height: 100vh; 
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            margin-bottom: 20px;
        }

        .logo {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .logo .youtube-text {
            color: var(--text-dark); 
            font-weight: 900;
        }

        .logo .downloader-text {
            color: var(--primary-blue); 
            font-weight: 900;
        }

        .menu-icon i {
            font-size: 24px;
            color: var(--text-dark);
            cursor: pointer;
        }

        .download-card {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        h2 {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-medium);
            margin-bottom: 10px;
        }
        
        .input-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        #youtube-url {
            padding: 12px 15px;
            border: 1px solid var(--card-border-grey);
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            background-color: var(--container-bg);
            color: var(--text-dark);
        }

        .generate-btn {
            padding: 15px;
            background-color: var(--primary-blue);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .preview-section {
            background-color: transparent;
            border: 2px dashed var(--card-border-grey);
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            min-height: 150px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .preview-section.loaded {
            border: none;
            padding: 0;
        }
        
        .initial-preview .video-icon {
            font-size: 30px;
            color: var(--text-medium);
            margin-bottom: 10px;
        }

        .initial-preview p {
            color: var(--text-medium);
            font-size: 14px;
        }

        .loaded-preview {
            display: none; 
            width: 100%;
        }
        
        .video-details {
            padding: 15px 0;
        }

        .video-info {
            margin-bottom: 10px; 
        }

        .video-info h3 {
            font-size: 18px;
            font-weight: 900;
            color: var(--primary-blue);
            margin: 0; 
            margin-bottom: 10px; 
        }
        
        #video-duration {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 5px; 
        }

        #video-duration p {
            font-size: 14px;
            color: var(--text-medium);
            font-weight: 600;
            margin: 0; 
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .thumbnail-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%;
            background-color: var(--card-border-grey);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .thumbnail-container img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .format-selection {
            display: flex;
            justify-content: space-around;
            gap: 5px;
            flex-wrap: nowrap;
            overflow-x: auto;
            margin-bottom: 0;
            padding: 0;
            scrollbar-width: none;
        }
        
        .format-selection::-webkit-scrollbar {
            display: none;
        }

        .format-btn {
            padding: 8px 10px;
            border: 1px solid var(--card-border-grey);
            background-color: var(--container-bg);
            color: var(--text-medium);
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            flex-shrink: 0;
            font-weight: 700;
            transition: all 0.2s;
        }
        
        .format-btn.active {
            background-color: var(--primary-blue);
            color: #ffffff;
            border-color: var(--primary-blue);
        }

        .download-options-container {
            margin-top: 15px;
            border-top: 1px solid var(--card-border-grey);
            padding-top: 15px;
            display: none;
            width: 100%;
        }
        
        .download-options-container h3 {
            font-size: 16px;
            color: var(--primary-blue);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .download-options-container h3 i {
            font-size: 18px;
        }

        .download-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--card-border-grey);
        }

        .download-btn-small {
            background-color: var(--primary-blue);
            color: #ffffff;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none; 
            font-family: 'Noto Sans', 'Hind Siliguri', sans-serif; 
        }

        .download-item a {
            text-decoration: none; 
        }
        
        .download-btn-small:hover {
            background-color: #3598e3;
        }
        
        .format-tag {
            background-color: var(--secondary-blue);
            color: #ffffff;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 10px;
        }

        .sidebar {
            position: fixed;
            top: 0;
            right: -300px;
            width: 280px;
            height: 100%;
            background-color: var(--sidebar-bg);
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
            z-index: 100;
            transition: right 0.3s ease-in-out;
            padding: 20px;
            overflow-y: auto;
        }

        .sidebar.open {
            right: 0;
        }

        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        #close-sidebar {
            font-size: 24px;
            color: var(--text-dark);
            cursor: pointer;
            padding: 5px;
        }
        
        .sidebar h3 {
            color: var(--primary-blue);
            font-size: 20px;
            font-weight: 700;
        }

        .sidebar-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            color: var(--sidebar-text);
            border-bottom: 1px solid var(--card-border-grey);
            cursor: pointer;
        }
        
        .sidebar-item:hover {
            background-color: var(--sidebar-hover);
            padding-left: 5px;
        }
        
        .sidebar-api-input {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid var(--card-border-grey);
            border-radius: 8px;
            background-color: var(--background-grey);
        }
        
        .api-input-group {
            display: flex;
            align-items: center;
            padding-bottom: 5px;
        }

        .api-input-group i {
            color: var(--primary-blue);
            margin-right: 10px;
        }

        #sidebar-api-key-input {
            flex-grow: 1;
            padding: 5px 0;
            border: none;
            outline: none;
            background-color: transparent;
            color: var(--text-dark);
            font-family: monospace;
        }

        #sidebar-api-key-toggle {
            cursor: pointer;
            color: var(--text-medium);
            padding-left: 10px;
        }

        .footer {
            text-align: center;
            margin-top: auto; 
            padding-top: 20px;
        }
    </style>
</head>
<body>

    <div class="container">
        <header class="header">
            <h1 class="logo">
                <span class="youtube-text">SOCIAL</span>
                <span class="downloader-text">DOWNLOADER</span>
            </h1>
            <div class="menu-icon" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </header>
        
        <main class="download-card">
            
            <section class="input-section">
                <h2>Paste any video URL (YouTube, TikTok, Instagram…)</h2>
                <div class="input-group">
                    <input type="text" id="youtube-url" placeholder="https://www.youtube.com/watch?v=... ???? ?????? ??????? ????? ????">
                    <button class="generate-btn" id="generate-btn">Generate Download Links</button>
                </div>
            </section>

            <section class="preview-section" id="preview-section">
                
                <div class="initial-preview" id="initial-preview">
                    <i class="fas fa-film video-icon"></i>
                    <p>Video Preview goes here</p>
                </div>

                <div class="loaded-preview" id="loaded-preview">
                    <div class="thumbnail-container">
                        <img id="video-thumbnail" src="" alt="Video Thumbnail">
                    </div>
                    
                    <div class="video-details">
                        <div class="video-info">
                            <h3 id="video-title"></h3>
                            <div id="video-duration"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="format-selection">
                <button class="format-btn active" data-format="mp4" data-type="video">.mp4</button>
                <button class="format-btn" data-format="webm" data-type="video">.webm</button>
                <button class="format-btn" data-format="mp3" data-type="audio">.mp3</button>
                <button class="format-btn" data-format="thumbnail" data-type="image">Thumb Download</button>
            </section>
            
            <section class="download-options-container" id="download-options-container"></section>
            
        </main>

        <footer class="footer">
            <p>Powered by Mujahidul hasan</p>
        </footer>
    </div>
    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Menu</h3>
            <span id="close-sidebar"><i class="fas fa-times"></i></span>
        </div>
        
        <div class="sidebar-item">
            <span><i class="fas fa-moon"></i> Day / Night Mode</span>
            <label class="switch">
                <input type="checkbox" id="dark-mode-toggle">
                <span class="slider"></span>
            </label>
        </div>
        
        <div class="sidebar-api-input">
            <h4><i class="fas fa-code"></i> API Add (Private)</h4>
            <div class="api-input-group">
                <input type="password" id="sidebar-api-key-input" placeholder="Enter your Social Downloader RapidAPI key..." value="">
                <i class="fas fa-eye-slash" id="sidebar-api-key-toggle"></i>
            </div>
            <div style="margin-top:8px;display:flex;gap:8px">
                <button id="save-api-btn" class="generate-btn" style="flex:1;padding:8px;font-size:13px">Save Key</button>
                <button id="clear-api-btn" class="generate-btn" style="flex:1;padding:8px;font-size:13px;background:#ff6b6b">Clear Key</button>
            </div>
            <div id="api-note" style="margin-top:8px;font-size:12px;color:var(--text-medium)">
                API key ????? ????????? ?????? ??? ???? ???? public-? ????? ????? ???
            </div>
        </div>
        
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const generateBtn = document.getElementById('generate-btn');
            const formatBtns = document.querySelectorAll('.format-btn');
            const initialPreview = document.getElementById('initial-preview');
            const loadedPreview = document.getElementById('loaded-preview');
            const previewSection = document.getElementById('preview-section');
            const videoTitle = document.getElementById('video-title');
            const videoDurationContainer = document.getElementById('video-duration');
            const videoThumbnail = document.getElementById('video-thumbnail');
            const youtubeUrlInput = document.getElementById('youtube-url');
            const downloadOptionsContainer = document.getElementById('download-options-container');
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menu-toggle');
            const closeSidebar = document.getElementById('close-sidebar');
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            const sidebarApiKeyInput = document.getElementById('sidebar-api-key-input');
            const sidebarApiKeyToggle = document.getElementById('sidebar-api-key-toggle');
            const body = document.body;
            const saveApiBtn = document.getElementById('save-api-btn');
            const clearApiBtn = document.getElementById('clear-api-btn');

            // ---- Social Download All In One API ----
            const API_HOST = "social-download-all-in-one.p.rapidapi.com";
            const API_URL = "https://" + API_HOST + "/v1/social/autolink";
            const STORAGE_KEY = 'social_downloader_rapidapi_key';

            // duration formatter (seconds -> HH:MM:SS)
            function formatDuration(seconds) {
                if (!seconds) return 'N/A';
                seconds = parseInt(seconds, 10);
                if (isNaN(seconds) || seconds < 0) return 'N/A';

                const h = Math.floor(seconds / 3600);
                const m = Math.floor((seconds % 3600) / 60);
                const s = Math.floor(seconds % 60);

                const pad = (num) => num.toString().padStart(2, '0');

                if (h > 0) {
                    return `${pad(h)}:${pad(m)}:${pad(s)}`;
                }
                return `${pad(m)}:${pad(s)}`;
            }

            // simple filesize formatter (bytes)
            function formatFileSize(bytes) {
                if (!bytes || isNaN(bytes)) return '';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
            }

            // open/close sidebar
            menuToggle.addEventListener('click', () => sidebar.classList.add('open'));
            closeSidebar.addEventListener('click', () => sidebar.classList.remove('open'));

            // dark mode toggle
            darkModeToggle.addEventListener('change', () => {
                body.classList.toggle('dark-mode', darkModeToggle.checked);
            });

            // show/hide API input value
            sidebarApiKeyToggle.addEventListener('click', () => {
                if (sidebarApiKeyInput.type === 'password') {
                    sidebarApiKeyInput.type = 'text';
                    sidebarApiKeyToggle.classList.remove('fa-eye-slash');
                    sidebarApiKeyToggle.classList.add('fa-eye');
                } else {
                    sidebarApiKeyInput.type = 'password';
                    sidebarApiKeyToggle.classList.remove('fa-eye');
                    sidebarApiKeyToggle.classList.add('fa-eye-slash');
                }
            });

            // load stored key (masked)
            function maskKey(k){
                if(!k) return '';
                if(k.length <= 10) return k;
                return k.slice(0,6) + '…' + k.slice(-4);
            }
            const stored = localStorage.getItem(STORAGE_KEY);
            if(stored){
                sidebarApiKeyInput.value = maskKey(stored);
                sidebarApiKeyInput.dataset.masked = 'true';
            }

            // save API key
            saveApiBtn.addEventListener('click', ()=>{
                let val = sidebarApiKeyInput.value.trim();
                if(!val){
                    alert('Paste your RapidAPI key in the field (click eye to reveal full if masked).');
                    return;
                }
                if(val.includes('…')){
                    const real = prompt('Your saved key looks masked. Paste the full RapidAPI key now to overwrite:');
                    if(!real) return;
                    localStorage.setItem(STORAGE_KEY, real.trim());
                    sidebarApiKeyInput.value = maskKey(real.trim());
                    sidebarApiKeyInput.dataset.masked = 'true';
                    alert('API key saved locally.');
                } else {
                    localStorage.setItem(STORAGE_KEY, val);
                    sidebarApiKeyInput.value = maskKey(val);
                    sidebarApiKeyInput.dataset.masked = 'true';
                    alert('API key saved locally.');
                }
            });

            // clear API key
            clearApiBtn.addEventListener('click', ()=>{
                if(confirm('Clear saved API key?')){
                    localStorage.removeItem(STORAGE_KEY);
                    sidebarApiKeyInput.value = '';
                    sidebarApiKeyInput.dataset.masked = '';
                    alert('API key cleared.');
                }
            });

            // tab handling (format buttons)
            formatBtns.forEach(btn=>{
                btn.addEventListener('click', ()=>{
                    if(initialPreview.style.display !== 'none') return; // preview ?? ????? ??? ???? ??
                    formatBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const format = btn.getAttribute('data-format');
                    const type = btn.getAttribute('data-type');
                    if(window.__lastApiResponse) {
                        renderDownloadOptionsFromResponse(window.__lastApiResponse, format, type);
                    }
                });
            });

            // main fetch handler (Social Downloader API)
            generateBtn.addEventListener('click', async ()=>{
                const rawUrl = youtubeUrlInput.value.trim();
                if(!rawUrl) return alert('Please paste any video URL (YouTube, TikTok, Instagram...)');

                const key = localStorage.getItem(STORAGE_KEY) || null;
                if(!key) {
                    alert('Please set your RapidAPI key in Settings (sidebar) first.');
                    sidebar.classList.add('open');
                    return;
                }

                generateBtn.textContent = 'Fetching...'; 
                generateBtn.disabled = true;
                downloadOptionsContainer.style.display = 'none';

                try {
                    const resp = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-RapidAPI-Host': API_HOST,
                            'X-RapidAPI-Key': key
                        },
                        body: JSON.stringify({ url: rawUrl })
                    });

                    const text = await resp.text();
                    let data;
                    try { data = JSON.parse(text); } catch(e){ data = null; }

                    if(!resp.ok){
                        console.error('API error', resp.status, text);
                        throw new Error('API request failed: ' + resp.status);
                    }

                    if(!data){
                        throw new Error('Invalid JSON from API.');
                    }

                    console.log('Social API response:', data);
                    window.__lastApiResponse = data;

                    if(data.error){
                        throw new Error(data.message || 'API returned error.');
                    }

                    // title / author / platform
                    const title = data.title || 'Untitled';
                    const author = data.author || data.unique_id || '';
                    const source = data.source || '';

                    // duration (API ???? ???????????, ???? ??????? ?????? ????)
                    let seconds = null;
                    if(typeof data.duration === 'number'){
                        if(data.duration > 10000) seconds = Math.round(data.duration / 1000);
                        else seconds = data.duration;
                    }

                    // thumbnail
                    const thumb = data.thumbnail || (data.thumbnails && data.thumbnails[0] && data.thumbnails[0].url) || '';

                    videoTitle.textContent = title;
                    const formattedDuration = formatDuration(seconds);

                    videoDurationContainer.innerHTML = `
                        <p><i class="fas fa-clock"></i> ${formattedDuration}</p>
                        ${source ? `<p><i class="fas fa-globe"></i> ${source.toUpperCase()}</p>` : ''}
                    `;

                    videoThumbnail.src = thumb || '';
                    previewSection.classList.add('loaded');
                    initialPreview.style.display = 'none';
                    loadedPreview.style.display = 'block';

                    // ?????? mp4 ?????
                    formatBtns.forEach(b => b.classList.remove('active'));
                    formatBtns[0].classList.add('active');
                    renderDownloadOptionsFromResponse(data, 'mp4', 'video');

                } catch (err) {
                    console.error(err);
                    alert('Failed to fetch video info: ' + err.message);
                } finally {
                    generateBtn.textContent = 'Generate Download Links'; 
                    generateBtn.disabled = false;
                }
            });

            // quality ???? ???????? ??? ??? sort ???? ???????
            function parseQualityValue(q){
                if(!q) return 0;
                const m = String(q).match(/(\d{2,4})p/i);
                if(m) return parseInt(m[1], 10);
                const kb = String(q).match(/(\d+)\s*kbps/i);
                if(kb) return parseInt(kb[1], 10);
                return 0;
            }

            function safe(u){
                return String(u||'').replace(/"/g,'&quot;').replace(/'/g,"'");
            }

            // format ??????? ??????? ???? render
            function renderDownloadOptionsFromResponse(data, format, type){
                downloadOptionsContainer.innerHTML = '';
                downloadOptionsContainer.style.display = 'none';
                if(!data) return;

                const medias = Array.isArray(data.medias) ? data.medias : [];

                let headerHtml = '';
                if(format === 'mp4') headerHtml = '<h3><i class="fas fa-video"></i> MP4 Download Options</h3>';
                else if(format === 'webm') headerHtml = '<h3><i class="fas fa-video"></i> WebM Download Options</h3>';
                else if(format === 'mp3') headerHtml = '<h3><i class="fas fa-music"></i> Audio Download Options</h3>';
                else if(format === 'thumbnail') headerHtml = '<h3><i class="fas fa-image"></i> Thumbnail Download Options</h3>';

                let list = [];

                if(format === 'mp4'){
                    list = medias.filter(m => {
                        const ext = (m.extension || '').toLowerCase();
                        return (m.type === 'video' || !m.type) && ext === 'mp4';
                    });
                    list.sort((a,b)=> parseQualityValue(a.quality) - parseQualityValue(b.quality));
                } else if(format === 'webm'){
                    list = medias.filter(m => {
                        const ext = (m.extension || '').toLowerCase();
                        return (m.type === 'video' || !m.type) && ext === 'webm';
                    });
                    list.sort((a,b)=> parseQualityValue(a.quality) - parseQualityValue(b.quality));
                } else if(format === 'mp3'){
                    list = medias.filter(m => {
                        const ext = (m.extension || '').toLowerCase();
                        const t   = (m.type || '').toLowerCase();
                        const q   = (m.quality || '').toLowerCase();
                        return t === 'audio' || ext === 'mp3' || q.includes('audio');
                    });
                    // kbps ??????? sort
                    list.sort((a,b)=> parseQualityValue(a.quality) - parseQualityValue(b.quality));
                }

                let html = headerHtml;

                if(format === 'thumbnail'){
                    const thumbUrl = data.thumbnail || (data.thumbnails && data.thumbnails[0] && data.thumbnails[0].url) || '';
                    if(thumbUrl){
                        html += `
                            <div class="download-item">
                                <div class="item-details">
                                    <span class="format-tag">IMG</span>
                                    <span style="font-weight:700">Default Thumbnail</span>
                                </div>
                                <div style="display:flex;gap:8px">
                                    <a class="download-btn-small" href="${safe(thumbUrl)}" download target="_blank" rel="noreferrer">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        `;
                    } else {
                        html += '<div style="color:var(--text-medium);padding:10px 0;">No thumbnail found for this link.</div>';
                    }
                } else {
                    if(list.length){
                        // ????????? URL+quality ?????
                        const seen = new Set();
                        list.forEach(m=>{
                            const u = m.url;
                            const q = m.quality || '';
                            const key = u + '|' + q;
                            if(!u || seen.has(key)) return;
                            seen.add(key);

                            const ext = (m.extension || '').toUpperCase();
                            const qText = m.quality || '';
                            const sizeText = m.data_size ? formatFileSize(m.data_size) : '';

                            html += `
                                <div class="download-item">
                                    <div class="item-details">
                                        <span class="format-tag">${ext || (format.toUpperCase())}</span>
                                        <span style="margin-right:10px;font-weight:700">${qText || 'Default'}</span>
                                        <span class="size" style="color:${sizeText? 'var(--text-dark)': 'var(--text-medium)'}">
                                            ${sizeText ? sizeText : ''}
                                        </span>
                                    </div>
                                    <div style="display:flex;gap:8px;align-items:center">
                                        <a class="download-btn-small" href="${safe(u)}" target="_blank" rel="noreferrer">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        if(format === 'mp3'){
                            html += '<div style="color:var(--text-medium);padding:10px 0;">No audio formats found for this link.</div>';
                        } else {
                            html += '<div style="color:var(--text-medium);padding:10px 0;">No ' + format.toUpperCase() + ' formats found for this link.</div>';
                        }
                    }
                }

                // debug button (optional)
                html += `
                    <div style="margin-top:12px;text-align:center;">
                        <button id="showRawBtn" class="download-btn-small" style="background:transparent;color:var(--text-dark);border:1px solid var(--card-border-grey)">
                            <i class="fas fa-code"></i> Show raw JSON (console)
                        </button>
                    </div>
                `;

                downloadOptionsContainer.innerHTML = html;
                downloadOptionsContainer.style.display = 'block';

                const rawBtn = document.getElementById('showRawBtn');
                if(rawBtn){
                    rawBtn.addEventListener('click', ()=>{
                        console.log('RAW API RESPONSE:', data);
                        alert('Raw JSON logged to console. DevTools ? Console ? ????? ??????');
                    });
                }

                // ??? ????? ???? ?????? ?????
                const items = downloadOptionsContainer.querySelectorAll('.download-item');
                if(items.length > 0){
                    items[items.length - 1].style.borderBottom = 'none';
                }
            }

            // initial state
            initialPreview.style.display = 'flex';
            loadedPreview.style.display = 'none';
            youtubeUrlInput.value = "";
        });
    </script>
</body>
</html>
