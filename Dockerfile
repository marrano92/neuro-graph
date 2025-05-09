FROM sail-8.4/app

# Install yt-dlp and its dependencies
RUN apt-get update && apt-get install -y \
    python3-full \
    ffmpeg \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && python3 -m venv /opt/yt-dlp-venv \
    && /opt/yt-dlp-venv/bin/pip install yt-dlp \
    && ln -s /opt/yt-dlp-venv/bin/yt-dlp /usr/local/bin/yt-dlp 

# Add custom supervisord configuration for Octane and Horizon
COPY supervisord-custom.conf /etc/supervisor/conf.d/supervisord.conf 