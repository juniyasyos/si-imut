#!/bin/bash

#==============================================================================
# Livewire Temporary Files Cleanup Script
# Description: Clean up old temporary files from Livewire uploads
#==============================================================================

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LIVEWIRE_TMP_DIR="$PROJECT_DIR/storage/app/private/livewire-tmp"

echo "Cleaning up Livewire temporary files older than 24 hours..."

# Remove files older than 24 hours
find "$LIVEWIRE_TMP_DIR" -type f -mtime +1 -delete

echo "✓ Cleanup completed!"
echo "Remaining files: $(find "$LIVEWIRE_TMP_DIR" -type f | wc -l)"
