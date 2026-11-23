<?php

namespace App\Services\Mikrotik;

use RuntimeException;

class RouterOsApiClient
{
    public function inspect(string $host, int $port, string $username, string $password, int $timeout = 5): array
    {
        $rows = $this->command($host, $port, $username, $password, '/system/resource/print', [], $timeout);
        $identity = $this->command($host, $port, $username, $password, '/system/identity/print', [], $timeout);
        $resource = $rows[0] ?? [];
        return [
            'identity' => $identity[0]['name'] ?? $host,
            'version' => $resource['version'] ?? 'Inconnue',
            'board' => $resource['board-name'] ?? 'Inconnu',
            'uptime' => $resource['uptime'] ?? 'Inconnu',
        ];
    }

    public function activeHotspotUsers(string $host, int $port, string $username, string $password): array
    {
        return $this->command($host, $port, $username, $password, '/ip/hotspot/active/print');
    }

    public function hotspotUsers(string $host, int $port, string $username, string $password): array
    {
        return $this->command($host, $port, $username, $password, '/ip/hotspot/user/print');
    }

    public function hotspotProfiles(string $host, int $port, string $username, string $password): array
    {
        return $this->command($host, $port, $username, $password, '/ip/hotspot/user/profile/print');
    }

    public function disconnectHotspotUser(string $host, int $port, string $username, string $password, string $sessionId): void
    {
        if (!preg_match('/^\*[0-9A-Fa-f]+$/', $sessionId)) {
            throw new RuntimeException('Identifiant de session RouterOS invalide.');
        }
        $this->command($host, $port, $username, $password, '/ip/hotspot/active/remove', ['=.id='.$sessionId]);
    }

    private function command(string $host, int $port, string $username, string $password, string $command, array $arguments = [], int $timeout = 7): array
    {
        $errno = 0;
        $error = '';
        $socket = @stream_socket_client(sprintf('tcp://%s:%d', $host, $port), $errno, $error, $timeout, STREAM_CLIENT_CONNECT);
        if (!is_resource($socket)) {
            throw new RuntimeException("Connexion impossible au routeur ({$error}).");
        }
        stream_set_timeout($socket, $timeout);
        try {
            $this->writeSentence($socket, ['/login', '=name='.$username, '=password='.$password]);
            $this->readReply($socket);
            $this->writeSentence($socket, array_merge([$command], $arguments));
            return $this->readReply($socket);
        } finally {
            fclose($socket);
        }
    }

    private function writeSentence($socket, array $words): void
    {
        foreach ($words as $word) {
            $this->writeLength($socket, strlen($word));
            $this->writeAll($socket, $word);
        }
        $this->writeAll($socket, chr(0));
    }

    private function readReply($socket): array
    {
        $rows = [];
        while (true) {
            $sentence = [];
            while (true) {
                $length = $this->readLength($socket);
                if ($length === 0) break;
                $sentence[] = $this->readExact($socket, $length);
            }
            if ($sentence === []) continue;
            $type = array_shift($sentence);
            if ($type === '!re') {
                $row = [];
                foreach ($sentence as $word) {
                    if (str_starts_with($word, '=') && str_contains(substr($word, 1), '=')) {
                        [$key, $value] = explode('=', substr($word, 1), 2);
                        $row[$key] = $value;
                    }
                }
                $rows[] = $row;
                continue;
            }
            if ($type === '!done') return $rows;
            if ($type === '!trap' || $type === '!fatal') {
                $attributes = [];
                foreach ($sentence as $word) {
                    if (str_starts_with($word, '=') && str_contains(substr($word, 1), '=')) {
                        [$key, $value] = explode('=', substr($word, 1), 2);
                        $attributes[$key] = $value;
                    }
                }
                throw new RuntimeException($attributes['message'] ?? 'Erreur retournée par RouterOS.');
            }
        }
    }

    private function writeLength($socket, int $length): void
    {
        if ($length < 0x80) $encoded = chr($length);
        elseif ($length < 0x4000) { $length |= 0x8000; $encoded = chr(($length >> 8) & 0xff).chr($length & 0xff); }
        elseif ($length < 0x200000) { $length |= 0xC00000; $encoded = chr(($length >> 16) & 0xff).chr(($length >> 8) & 0xff).chr($length & 0xff); }
        elseif ($length < 0x10000000) { $length |= 0xE0000000; $encoded = chr(($length >> 24) & 0xff).chr(($length >> 16) & 0xff).chr(($length >> 8) & 0xff).chr($length & 0xff); }
        else $encoded = chr(0xF0).pack('N', $length);
        $this->writeAll($socket, $encoded);
    }

    private function readLength($socket): int
    {
        $first = ord($this->readExact($socket, 1));
        if (($first & 0x80) === 0) return $first;
        if (($first & 0xC0) === 0x80) return (($first & ~0xC0) << 8) + ord($this->readExact($socket, 1));
        if (($first & 0xE0) === 0xC0) return (($first & ~0xE0) << 16) + (ord($this->readExact($socket, 1)) << 8) + ord($this->readExact($socket, 1));
        if (($first & 0xF0) === 0xE0) return (($first & ~0xF0) << 24) + (ord($this->readExact($socket, 1)) << 16) + (ord($this->readExact($socket, 1)) << 8) + ord($this->readExact($socket, 1));
        if ($first === 0xF0) return unpack('N', $this->readExact($socket, 4))[1];
        throw new RuntimeException('Réponse RouterOS invalide.');
    }

    private function readExact($socket, int $length): string
    {
        $data = '';
        while (strlen($data) < $length) {
            $chunk = fread($socket, $length - strlen($data));
            if ($chunk === false || $chunk === '') {
                $meta = stream_get_meta_data($socket);
                throw new RuntimeException(!empty($meta['timed_out']) ? 'Délai RouterOS dépassé.' : 'Connexion RouterOS interrompue.');
            }
            $data .= $chunk;
        }
        return $data;
    }

    private function writeAll($socket, string $data): void
    {
        $offset = 0;
        while ($offset < strlen($data)) {
            $written = fwrite($socket, substr($data, $offset));
            if ($written === false || $written === 0) throw new RuntimeException('Écriture API RouterOS impossible.');
            $offset += $written;
        }
    }
}
