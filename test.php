<?php

class ServerInfo {
    var $connected;
    var $resource;
    var $ip;
    var $port;
    var $raw;

    function ServerInfo() {
        $this->connected = false;
        $this->resource = false;
    }

    function connect( $address, $port = 27015) {
        $this->disconnect();
        $this->port = (int)$port;
        /*$this->ip = gethostbyname( $address );*/
        $this->ip = $address;
        $this->resource = fsockopen( 'udp://' . $this->ip, $this->port, $errno, $errstr );
            if (!$this->resource) {
                echo "ERROR: $errno - $errstr<br>\n";
            } else {
                echo ('connect ... Ok!');
                $this->connected = true;
                stream_set_timeout( $this->resource, 1);
            }
    }

    function disconnect() {
        if( $this->connected ) {
            fclose( $this->resource );
            $this->connected = false;
        }
    }

    function getByte() {
        $return = ord( $this->raw[0] );
        $this->raw = substr( $this->raw , 1 );
        return $return;
    }

    function getShort() {
        $return = (ord($this->raw[1]) << 8) | ord($this->raw[0]);
        $this->raw = substr( $this->raw , 2 );
        return $return;
    }

    function getLong() {
        $lo = (ord($this->raw[1]) << 8) | ord($this->raw[0]);
        $hi = (ord($this->raw[3]) << 8) | ord($this->raw[2]);
        $this->raw = substr( $this->raw , 4 );
        return ($hi << 16) | $lo;
    }

    function getFloat() {
        $lo = (ord($this->raw[1]) << 8) | ord($this->raw[0]);
        $hi = (ord($this->raw[2]) << 8) | ord($this->raw[3]);
        $this->raw = substr( $this->raw , 4 );
        return ($hi << 16) | $lo;
    }

    function getString() {
        $str = "";
        $i = 0;
        $n = strlen( $this->raw );
        while( ( $this->raw[$i] != "\0" ) && ( $i < $n ) ) {
            $str .= $this->raw[$i];
            $i++;
        }
        $this->raw = substr( $this->raw , strlen( $str ) + 1);
        return $str;
    }

    function getInfo() {
        if( !$this->connected ) {
            echo('connect ... offline!');
            return false;
        } else {
            echo('connect ... online!');
        }
        $return = array();
        fwrite( $this->resource, "\xFF\xFF\xFF\xFFTSource Engine Query\0" );
        fread( $this->resource, 4 );
        $status = socket_get_status( $this->resource );
        print_r($status);
        if( $status['unread_bytes'] > 0 ) {
			echo "read\n";
            $this->raw = fread( $this->resource, $status['unread_bytes'] );
            $tmp = $this->getByte();
            if( $tmp == 0x6d ) {
                $this->getString();
                $return['name'] = $this->getString();
                $return['map'] = $this->getString();
                $return['directory'] = $this->getString();
                $return['desсriрtion'] = $this->getString();
                $return['players'] = $this->getByte();
                $return['max_players'] = $this->getByte();
                $return['version'] = $this->getByte();
                $this->getByte();
                $tmp = chr( $this->getByte() );
                if( $tmp == 'l' )
                    $return['os'] = 'Linux';
                else
                    $return['os'] = 'Windows';
                if( $this->getByte() == 0x01 )
                    $return['password'] = 'yes';
                else
                    $return['password'] = 'no';
                if( $this->getByte() == 0x01 ) {
                    $this->getString();
                    $this->getString();
                    $this->raw = substr( $this->raw, 11 );
                }
                if( $this->getByte() == 0x01 )
                    $return['secure'] = 'yes';
                else
                    $return['secure'] = 'no';
                $return['bots'] = $this->getByte();
            } elseif( $tmp == 0x49 ) {
                $return['version'] = $this->getByte();
                $return['name'] = $this->getString();
                $return['map'] = $this->getString();
                $return['directory'] = $this->getString();
                $return['desсriрtion'] = $this->getString();
                $this->raw = substr( $this->raw, 2 );
                $return['players'] = $this->getByte();
                $return['max_players'] = $this->getByte();
                $return['bots'] = $this->getByte();
                $this->getByte();
                $tmp = chr( $this->getByte() );
                if( $tmp == 'l' )
                    $return['os'] = 'Linux';
                else
                    $return['os'] = 'Windows';
                if( $this->getByte() == 0x01 )
                    $return['password'] = 'yes';
                else
                    $return['password'] = 'no';
                if( $this->getByte() == 0x01 )
                    $return['secure'] = 'yes';
                else
                    $return['secure'] = 'no';
            }
        } else {
			echo "disco\n";
            $this->disconnect();
            return false;
        }
        return $return;
    }

    function getPlayers() {
        $return = array();
        if( !$this->connected ) {
			echo "not connected";
            return $return;
          }

        fwrite( $this->resource, "\xFF\xFF\xFF\xFF\x55" . $this->getChallenge() );
        fread( $this->resource, 4 );
        $status = socket_get_status( $this->resource );
        if( $status['unread_bytes'] > 0 ) {
            $this->raw = fread( $this->resource, $status['unread_bytes'] );
            if( $this->getByte() == 0x44 ) {
                $num = $this->getByte();
                for($i = 0; $i < $num; $i++ ) {
                    $tmp = $this->getByte();
                    $name = $this->getString();
                    $kills = $this->getLong();
                    $time = @unpack( "f1float", $this->raw );
                    $this->raw = substr( $this->raw, 4 );
                    $return[] = array(
                        'name' => $name,
                        'kills' => $kills,
                        'time' => gmdate( "H:i:s", (int)$time['float'] )
                    );
                }
            }
        }
        return $return;
    }

    function getChallenge() {
        if( !$this->connected )
            return false;
        fwrite( $this->resource, "\xFF\xFF\xFF\xFF\x55\xFF\xFF\xFF\xFF" );
        fread( $this->resource, 5 );
        return fread( $this->resource, 4 );
    }

    function getRules() {
        $return = array();
        if( !$this->connected )
            return $return;

        fwrite( $this->resource, "\xFF\xFF\xFF\xFF\x56" . $this->getChallenge() );
        fread( $this->resource, 13 );
        $status = socket_get_status( $this->resource );
        if( $status['unread_bytes'] > 0 ) {
            $this->raw = fread( $this->resource, $status['unread_bytes'] );
            if( $this->getByte() == 0x45 ) {
                $this->getShort();
                $arr = explode( "\0", $this->raw );
                $num = count( $arr );
                for( $i = 0; $i < $num ; $i++ )
                    $return[$arr[$i]] = $arr[++$i];
            }
        }
        return $return;
    }
}

$info = new ServerInfo();
$info->connect("counter-strike.kiev.ua", 27015 );
print_r( $info->getInfo() );
#print_r( $info->getPlayers() );
#print_r( $info->getRules() );
$info->disconnect();


?>
