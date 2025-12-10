#!/bin/bash

# Script untuk setup MinIO dan membuat bucket
echo "=== MinIO Setup Script ==="

# Check if docker-compose is running
if ! docker-compose ps | grep -q "minio"; then
    echo "Starting MinIO container..."
    docker-compose up -d minio
    echo "Waiting for MinIO to be ready..."
    sleep 10
else
    echo "MinIO container already running"
fi

# Install MinIO Client (mc) if not exists
if ! command -v mc &> /dev/null; then
    echo "Installing MinIO Client..."
    wget https://dl.min.io/client/mc/release/linux-amd64/mc -O /tmp/mc
    chmod +x /tmp/mc
    MC_CMD="/tmp/mc"
else
    MC_CMD="mc"
fi

# Configure MinIO Client
echo "Configuring MinIO Client..."
$MC_CMD alias set local http://localhost:9000 minioadmin minioadmin

# Create bucket if not exists
BUCKET_NAME="siimut"
echo "Creating bucket: $BUCKET_NAME"
$MC_CMD mb local/$BUCKET_NAME --ignore-existing

# Set bucket policy to public for read access
echo "Setting bucket policy to public..."
$MC_CMD anonymous set download local/$BUCKET_NAME

# List buckets
echo "Current buckets:"
$MC_CMD ls local

echo ""
echo "=== MinIO Setup Complete ==="
echo "MinIO Console: http://localhost:9001"
echo "MinIO API: http://localhost:9000"
echo "Username: minioadmin"
echo "Password: minioadmin"
echo "Bucket: $BUCKET_NAME"
echo ""
echo "Don't forget to update your .env file with MinIO configuration!"
