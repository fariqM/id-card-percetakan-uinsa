<?php

/**
 * Secure Encoder Functions for Laravel By Fariq PUSTIPD
 * 
 * Usage:
 * require __DIR__ . '/secure.php';
 * $encoded = my_encode($data);
 * $decoded = my_decode($encoded);
 * 
 * No external libraries required - uses only PHP core functions
 */

if (!function_exists('my_encode')) {
    /**
     * Encode a string using AES-256-GCM encryption
     * 
     * @param string $data The data to encode
     * @param string|null $password Optional password (uses app key if null)
     * @return string URL-safe base64 encoded encrypted data
     */
    function my_encode(string $data, string $password = null): string
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Data cannot be empty');
        }
        
        // Use Laravel's APP_KEY if no password provided
        if ($password === null) {
            $password = _get_app_key();
        }
        
        if (empty($password)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }
        
        // Constants
        $algorithm = 'aes-256-gcm';
        $keyLength = 32;   // 256 bits
        $ivLength = 12;    // 96 bits for GCM
        $saltLength = 32;  // 256 bits
        $iterations = 100000;
        
        // Generate random salt for key derivation
        $salt = _secure_random_bytes($saltLength);
        
        // Derive key using PBKDF2
        $key = hash_pbkdf2('sha256', $password, $salt, $iterations, $keyLength, true);
        
        // Generate random IV
        $iv = _secure_random_bytes($ivLength);
        
        // Encrypt the data
        $encrypted = openssl_encrypt(
            $data,
            $algorithm,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($encrypted === false) {
            throw new Exception('Encryption failed');
        }
        
        // Combine salt + IV + tag + encrypted data
        $combined = $salt . $iv . $tag . $encrypted;
        
        // Return URL-safe base64 encoded result
        return _url_safe_base64_encode($combined);
    }
}

if (!function_exists('my_decode')) {
    /**
     * Decode a string encrypted with my_encode function
     * 
     * @param string $encodedData URL-safe base64 encoded encrypted data
     * @param string|null $password Optional password (uses app key if null)
     * @return string The original decrypted data
     */
    function my_decode(string $encodedData, string $password = null): string
    {
        if (empty($encodedData)) {
            throw new InvalidArgumentException('Encoded data cannot be empty');
        }
        
        // Use Laravel's APP_KEY if no password provided
        if ($password === null) {
            $password = _get_app_key();
        }
        
        if (empty($password)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }
        
        // Constants
        $algorithm = 'aes-256-gcm';
        $keyLength = 32;   // 256 bits
        $ivLength = 12;    // 96 bits for GCM
        $tagLength = 16;   // 128 bits
        $saltLength = 32;  // 256 bits
        $iterations = 100000;
        
        // Decode from URL-safe base64
        $combined = _url_safe_base64_decode($encodedData);
        
        if ($combined === false) {
            throw new Exception('Invalid URL-safe base64 encoding');
        }
        
        // Check minimum length
        $minLength = $saltLength + $ivLength + $tagLength;
        if (strlen($combined) < $minLength) {
            throw new Exception('Invalid encrypted data format');
        }
        
        // Extract components
        $salt = substr($combined, 0, $saltLength);
        $iv = substr($combined, $saltLength, $ivLength);
        $tag = substr($combined, $saltLength + $ivLength, $tagLength);
        $encrypted = substr($combined, $saltLength + $ivLength + $tagLength);
        
        // Derive the same key using the extracted salt
        $key = hash_pbkdf2('sha256', $password, $salt, $iterations, $keyLength, true);
        
        // Decrypt the data
        $decrypted = openssl_decrypt(
            $encrypted,
            $algorithm,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($decrypted === false) {
            throw new Exception('Decryption failed - invalid password or corrupted data');
        }
        
        return $decrypted;
    }
}

if (!function_exists('my_generate_key')) {
    /**
     * Generate a secure random key/password
     * 
     * @param int $length Key length (minimum 12)
     * @return string Random key
     */
    function my_generate_key(int $length = 32): string
    {
        if ($length < 12) {
            throw new InvalidArgumentException('Key length must be at least 12 characters');
        }
        
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        $key = '';
        $charCount = strlen($chars) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $key .= $chars[_secure_random_int(0, $charCount)];
        }
        
        return $key;
    }
}

// Helper functions (private)
if (!function_exists('_get_app_key')) {
    /**
     * Get Laravel application key or generate a default key
     * 
     * @return string Application key
     */
    function _get_app_key(): string
    {
        // Try to get Laravel's APP_KEY
        if (function_exists('env')) {
            $appKey = env('APP_KEY');
            if (!empty($appKey)) {
                // Remove 'base64:' prefix if present
                if (strpos($appKey, 'base64:') === 0) {
                    return base64_decode(substr($appKey, 7));
                }
                return $appKey;
            }
        }
        
        // Try $_ENV
        if (isset($_ENV['APP_KEY']) && !empty($_ENV['APP_KEY'])) {
            $appKey = $_ENV['APP_KEY'];
            if (strpos($appKey, 'base64:') === 0) {
                return base64_decode(substr($appKey, 7));
            }
            return $appKey;
        }
        
        // Try getenv
        $appKey = getenv('APP_KEY');
        if ($appKey !== false && !empty($appKey)) {
            if (strpos($appKey, 'base64:') === 0) {
                return base64_decode(substr($appKey, 7));
            }
            return $appKey;
        }
        
        // Fallback: generate a consistent key based on server info
        $serverData = php_uname() . ($_SERVER['SERVER_NAME'] ?? 'localhost') . __FILE__;
        return hash('sha256', $serverData, true);
    }
}

if (!function_exists('_url_safe_base64_encode')) {
    /**
     * URL-safe base64 encoding
     * Replaces + with -, / with _, and removes padding =
     * 
     * @param string $data Data to encode
     * @return string URL-safe base64 encoded string
     */
    function _url_safe_base64_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('_url_safe_base64_decode')) {
    /**
     * URL-safe base64 decoding
     * Replaces - with +, _ with /, and adds padding if needed
     * 
     * @param string $data URL-safe base64 encoded string
     * @return string|false Decoded data or false on failure
     */
    function _url_safe_base64_decode(string $data)
    {
        // Replace URL-safe characters back to standard base64
        $data = strtr($data, '-_', '+/');
        
        // Add padding if needed
        $padLength = 4 - (strlen($data) % 4);
        if ($padLength !== 4) {
            $data .= str_repeat('=', $padLength);
        }
        
        return base64_decode($data, true);
    }
}

if (!function_exists('_secure_random_bytes')) {
    /**
     * Generate cryptographically secure random bytes
     * 
     * @param int $length Number of bytes
     * @return string Random bytes
     */
    function _secure_random_bytes(int $length): string
    {
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }
        
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length, $strong);
            if ($strong && $bytes !== false) {
                return $bytes;
            }
        }
        
        // Fallback (less secure)
        $bytes = '';
        for ($i = 0; $i < $length; $i++) {
            $bytes .= chr(mt_rand(0, 255));
        }
        return $bytes;
    }
}

if (!function_exists('_secure_random_int')) {
    /**
     * Generate cryptographically secure random integer
     * 
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @return int Random integer
     */
    function _secure_random_int(int $min, int $max): int
    {
        if (function_exists('random_int')) {
            return random_int($min, $max);
        }
        
        // Fallback using random_bytes
        $range = $max - $min + 1;
        $bytes = _secure_random_bytes(4);
        $val = unpack('N', $bytes)[1];
        return $min + ($val % $range);
    }
}

if (!function_exists('my_check_support')) {
    /**
     * Check if the encryption algorithm is supported
     * 
     * @return bool True if supported
     */
    function my_check_support(): bool
    {
        return in_array('aes-256-gcm', openssl_get_cipher_methods(), true);
    }
}

// Initialize check
if (!my_check_support()) {
    throw new Exception('AES-256-GCM encryption is not supported on this system. Please ensure OpenSSL extension is installed and up to date.');
}