<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Simple WebSocket server implementation
class ChatWebSocketServer {
    private $clients = [];
    private $socket;
    
    public function __construct($host = 'localhost', $port = 8080) {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, $host, $port);
        socket_listen($this->socket);
        
        echo "WebSocket server started on $host:$port\n";
    }
    
    public function run() {
        while (true) {
            $read = array_merge([$this->socket], $this->clients);
            $write = null;
            $except = null;
            
            if (socket_select($read, $write, $except, 0, 10) < 1) {
                continue;
            }
            
            if (in_array($this->socket, $read)) {
                $client = socket_accept($this->socket);
                $this->clients[] = $client;
                $this->performHandshake($client);
                echo "New client connected\n";
            }
            
            foreach ($this->clients as $key => $client) {
                if (in_array($client, $read)) {
                    $data = socket_read($client, 1024);
                    
                    if ($data === false) {
                        unset($this->clients[$key]);
                        socket_close($client);
                        echo "Client disconnected\n";
                        continue;
                    }
                    
                    $message = $this->decode($data);
                    if ($message) {
                        $this->broadcast($message);
                    }
                }
            }
        }
    }
    
    private function performHandshake($client) {
        $request = socket_read($client, 5000);
        preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $request, $matches);
        
        if (empty($matches[1])) {
            return false;
        }
        
        $key = $matches[1];
        $responseKey = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        
        $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept: $responseKey\r\n\r\n";
        
        socket_write($client, $response, strlen($response));
        return true;
    }
    
    private function decode($data) {
        $length = ord($data[1]) & 127;
        
        if ($length == 126) {
            $masks = substr($data, 4, 4);
            $data = substr($data, 8);
        } elseif ($length == 127) {
            $masks = substr($data, 10, 4);
            $data = substr($data, 14);
        } else {
            $masks = substr($data, 2, 4);
            $data = substr($data, 6);
        }
        
        $text = '';
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }
        
        return $text;
    }
    
    private function encode($text) {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);
        
        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } elseif ($length >= 65536) {
            $header = pack('CCNN', $b1, 127, $length);
        }
        
        return $header . $text;
    }
    
    private function broadcast($message) {
        $encoded = $this->encode($message);
        
        foreach ($this->clients as $key => $client) {
            if (socket_write($client, $encoded, strlen($encoded)) === false) {
                unset($this->clients[$key]);
                socket_close($client);
            }
        }
    }
}

// Start the WebSocket server
if (php_sapi_name() === 'cli') {
    $server = new ChatWebSocketServer();
    $server->run();
} else {
    echo "This script must be run from command line\n";
}
?>
