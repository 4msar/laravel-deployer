# Download the build file and make it executable and 
# move to /usr/local/bin (ask before doing this)
# Usage: ./install.sh | curl -sSL https://github.com/4msar/laravel-deployer/raw/refs/heads/main/install.sh | bash

FILE_NAME="laravel-deployer"
URL="https://github.com/4msar/laravel-deployer/raw/refs/heads/main/builds/laravel-deployer"
DESTINATION="/usr/local/bin/$FILE_NAME"

echo "This script will download the laravel-deployer build file and move it to $DESTINATION"

read -p "Do you want to continue? (y/n): " choice < /dev/tty

if [[ "$choice" != "y" && "$choice" != "Y" ]]; then
    echo "Installation cancelled."
    exit 0
fi

curl -L "$URL" -o "$FILE_NAME"
if [ $? -ne 0 ]; then
    echo "Failed to download the file."
    exit 1
fi

chmod +x "$FILE_NAME"
if [ $? -ne 0 ]; then
    echo "Failed to make the file executable."
    exit 1
fi

echo "Moving $FILE_NAME to $DESTINATION (you may be prompted for your password)..."
read -p "Do you want to continue? (y/n): " choice < /dev/tty
if [[ "$choice" != "y" && "$choice" != "Y" ]]; then
    echo "The file is downloaded and made executable, but not moved."
    exit 0
fi

sudo mv "$FILE_NAME" "$DESTINATION"
if [ $? -ne 0 ]; then
    echo "Failed to move the file to $DESTINATION."
    exit 1
fi

echo "Installation completed successfully. You can now use '$FILE_NAME' from anywhere."