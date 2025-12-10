#!/bin/bash

# Script untuk setup MinIO bucket dengan credentials dari .env
echo "=== MinIO Bucket Setup ==="

# Read credentials from .env
if [ -f .env ]; then
    export $(cat .env | grep -E '^AWS_ACCESS_KEY_ID=' | xargs)
    export $(cat .env | grep -E '^AWS_SECRET_ACCESS_KEY=' | xargs)
    export $(cat .env | grep -E '^AWS_BUCKET=' | xargs)
    export $(cat .env | grep -E '^AWS_ENDPOINT=' | xargs)
fi

echo "Using credentials:"
echo "Access Key: $AWS_ACCESS_KEY_ID"
echo "Bucket: $AWS_BUCKET"
echo "Endpoint: $AWS_ENDPOINT"

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
$MC_CMD alias set local $AWS_ENDPOINT $AWS_ACCESS_KEY_ID $AWS_SECRET_ACCESS_KEY

# Create bucket if not exists
echo "Creating bucket: $AWS_BUCKET"
$MC_CMD mb local/$AWS_BUCKET --ignore-existing

# Set bucket policy to public for read access
echo "Setting bucket policy to public..."
$MC_CMD anonymous set download local/$AWS_BUCKET

# List buckets
echo "Current buckets:"
$MC_CMD ls local

# Test upload
echo "Testing upload..."
echo "Test file content" > /tmp/test-minio.txt
$MC_CMD cp /tmp/test-minio.txt local/$AWS_BUCKET/test/test-file.txt
rm /tmp/test-minio.txt

echo ""
echo "=== MinIO Setup Complete ==="
echo "MinIO Console: http://localhost:9001"
echo "MinIO API: http://localhost:9000"
echo "Bucket: $AWS_BUCKET"
