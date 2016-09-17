<?php

	/*************************************************************************************************************
	* Cache Class (Disk cache, Memcached, Memcache and APC) 
	* @author 	Jaka Prasnikar - https://prahec.com/
	* @version 	2.1 (29.08.2015)
	************************************************************************************************************ */
	class cache {

		// Protected vars
		protected $opts, $obj, $store = false, $startup = false;

		/* Initiate new cache object using supplied options
		* @param path, ext, encrypt, mode, prefix
		==================================================================================================================== */
		function __construct ( $options = array() ) {

			// Default Cache class options
			$this->opts = $options + array(
				'path'			=>	'cache/',				// Location where to cache items
				'ext'			=>	'.cache',  				// Disk cache file extension
				'encrypt'		=> 	false,    				// Disk cache basic file encryption
				'mode'			=>	'disk',            		// Modes: disk, apc, memcache, memcached
				'options'		=>	array(),				// Nothing yet?
				'prefix'		=>	''     					// Key prefix (all modes)
			);


			// Check settings & caching mode
			if ( $this->checkSettings() ) {

				// Start cache garbage collector
				$this->startup = true;
				$this->garbage( 'init' );

			}


		}


		/* Check all set options and try to connect to memcache/memcached server
		==================================================================================================================== */
		private function checkSettings() {

			// Set $check to false
			$check = false;

			// Various tests & connect
			switch ($this->opts['mode']) {

				case 'disk': // disk cache
					$check = true;
					break;

				case 'apc': // php_apc
					if( extension_loaded( 'apc' ) AND ini_get( 'apc.enabled' ) ) $check = true;
					break;

				case 'memcache': // php_memcache

					if ( extension_loaded( 'memcache' ) ) { ## Check if loaded

						## Obj
						$this->obj = new Memcache;

						if ( preg_match( '/(.*):(.*)/', $this->opts['path'] ) === false ) { ## Use socket

							if ($this->obj->addServer($this->opts['path'], 0, true)) {
								$check = true;
							}

						} else { # Use host:port

							preg_match( '/(.*):(.*)/', $this->opts['path'], $host );
							if ( $this->obj->addServer( $host[1], $host[2], true ) ) {
								$check = true;
							}

						}
					}

					break;

				case 'memcached': // php_memcached

					if ( extension_loaded( 'memcached' ) ) { ## Check if loaded

						## Obj
						$this->obj = new Memcached();
						$servers = $this->obj->getServerList();

						if ( count($servers) > 1 ) { ## Stop if there is already servers added

							$check = true;

						} else if ( preg_match( '/(.*):(.*)/', $this->opts['path'] ) === false ) { ## Use socket

							if ( $this->obj->addServer($this->opts['path'], 0, true ) )
								$check = true;

						} else { # Use host:port

							preg_match( '/(.*):(.*)/', $this->opts['path'], $host ); ## match 127.0.0.1:11211
							if ( $this->obj->addServer( $host[1], $host[2], true ) )
								$check = true;

						}


						// Extra options for memcached
						if ( count( $this->opts['options'] ) > 1 ) {

							foreach ( $this->opts['options'] as $opt => $val ) {
								$this->obj->setOption( $opt, $val );
							}

						}

					}

					break;


			}


			// Return true on success or false on failure
			return $check;

		}


		/* Cache dictonary & garbage collector
		* @param clean, check, delete or any value will re-read store
		* @param name of the key you wish to check/delete from store
		* @param requred for storing cache for DISK mode
		==================================================================================================================== */
		public function garbage ( $act = 'clean', $key = '', $ttl = '600' ) {

			// If class failed to startup, quit now!
			if ( $this->startup == false ) return false;

			// Switch actions
			switch ($act) {

				case 'clean': ## Clean whole cache including dictonary/garbage

					if ( is_array( $this->store ) ) {

						$cleanstatus = array();
						foreach ( $this->store as $key => $ttl ) {
							$cleanstatus[] = $key;
							$this->delete( $key );
						}

						// Clear script cache
						unset( $this->store );
						$this->store = array();

					}

					return $cleanstatus;
					break;


				case 'check': ## Check key existance/expiration

					if ( $this->store[$key] != '0' AND ( !isset( $this->store[$key] ) OR time() > $this->store[$key]) ) 
						return false; 
					else
						return true;

					break;


				case 'delete': ## Delete key from Garbage

					if ( $this->store === false ) {

						return false;

					}  else { ## Add key -> ttl to cache_status

						unset( $this->store[$key] );
						$this->set( 'cache_store', $this->store, 0 );
						return true;

					}

					break;


				default: // Init, etc...

					if ( $this->store == false ) { ## Only if false

						$v = $this->get("cache_store");
						if ( $v !== false && is_array($v) )
							$this->store = $v;
						else
							$this->store = array();

					}

					break;

			}

			// Garbage - done

		}


		/* Set specific cache value (or update) by supplied key
		* @return true/false
		* @param name of the key to store
		* @param value to store (string, array, int, float or object)
		* @param how long cache should be stored (0 = unlimited)
		==================================================================================================================== */
		public function set ( $key, $data, $ttl = '600' ) {

			// If class failed to startup, quit now!
			if ( $this->startup == false ) return false;


			// Prefix / Default response
			$name = $this->parseKey( $key );
			$resp = false;


			// Various Modes / Actions
			switch ( $this->opts['mode'] ) {


				case 'apc': // php_apc
					$resp = apc_store( $name, $data, $ttl );
					break;


				case 'memcache': // php_memcache

					if ( !$resp = $this->obj->replace($name, $data, false, $ttl) ) { ## Try to replace key, else make new one
						$resp = $this->obj->set( $name, $data, false, $ttl );
					}

					break;


				case 'memcached': // php_memcached

					if (!$resp = $this->obj->replace($name, $data, $ttl)) { ## Try to replace key, else make new one
						$resp = $this->obj->set($name, $data, $ttl);
					}

					break;


				default: // Disk cache

					// Encryption
					if ( $this->opts['encrypt'] === true ) {
						$data = base64_encode();
					}


					// Check if path exists
					if ( !is_dir( $this->opts['path'] ) ) { // if not create it recursively
						if ( !mkdir( $this->opts['path'], 0777, true ) ) {
							return false;
						}
					}


					// Write cache if its writtable
					if ( is_writable( $this->opts['path'] ) ) {

						// Serialize arrays & objects
						if ( is_array( $data ) OR is_object( $data ) ) {
							$data = serialize( $data );
						}

						file_put_contents( $this->opts['path'] . $name . $this->opts['ext'], $data );
						$resp = true;
					}

					break;


			}

			// Add/Update Garbage and return
			if ( $key == 'cache_store' ) {

				return $resp;

			} else {

				$this->store[$key] = ( ( $ttl == '0' ) ? 0 : time() + $ttl );
				return $resp;

			}

			// Set - done

		}


		/* Get data stored value from cache
		==================================================================================================================== */
		public function get( $key ) {

			// If class failed to startup, quit now!
			if ( $this->startup == false ) return false;

			// Use Prefix
			$name = $this->parseKey( $key );

			// Various Modes / Actions
			switch ( $this->opts['mode'] ) {


				case 'apc': // php_apc

					$apc = apc_fetch( $name, $suc ); ## Try 
					if( $suc ) { return $apc; } else { return false; }

					break;


				case 'memcache': // php_memcache
					return $this->obj->get( $name );
					break;


				case 'memcached': // php_memcached
					return $this->obj->get( $name );
					break;


				default: // Disk cache

					// Check if cache exists
					if ( is_file( $this->opts['path'] . $name . $this->opts['ext'] ) ) {

						if ( $key == 'cache_store' OR $this->garbage('check', $key) === true ) { // Check if cache is expired

							$data 		= file_get_contents ( $this->opts['path'] . $name . $this->opts['ext'] );  	## Open file
							$data		= ( ( $this->opts['encrypt'] === true ) ? base64_decode( $data ) : $data );		## Encryption
							$serialized = @unserialize ( $data );

							if ( $serialized !== false ) { // Object, Array

								return $serialized;

							} else { // Nope, just data

								return $data;

							}

						}

					}

					// Something wrong
					return false;

					break;


			}

			// Get - done

		}


		/* Delete cached value by supplied key
		==================================================================================================================== */
		public function delete ( $key ) {

			// If class failed to startup, quit now!
			if ( $this->startup == false ) return false;

			// Use prefix
			$name = $this->parseKey( $key );

			// Delete key from temporary store
			if ( $key != 'cache_store' ) $this->garbage ( 'delete', $key );

			// Various Modes / Actions
			switch ( $this->opts['mode'] ) {


				case 'apc': // php_apc
					return apc_delete( $name );
					break;


				case 'memcache': // php_memcache
					return $this->obj->delete( $name );
					break;


				case 'memcached': // php_memcached
					return $this->obj->delete( $name );
					break;


				default: // Disk cache

					if ( is_file($this->opts['path'] . $name . $this->opts['ext'] ) ) {

						// Del cache
						@unlink ( $this->opts['path'] . $name . $this->opts['ext'] );
						return true;

					} 

					break;


			}

			// Delete - done

		}


		/* Delete all keys via REGEX with help of cache store
		** @param PHP REGEX
		==================================================================================================================== */
		public function deleteAll ( $regex = '.*' ) {

			// If class failed to startup, quit now!
			if ( $this->startup == false ) return false;

			// Default variable
			$deleted = array();

			// Since version 2.3 we use cachestore for all cache modes
			if ( $this->store != false && is_array( $this->store ) ) {

				foreach ( $this->store as $key => $expire ) {

					if ( $key == 'cache_store' ) continue; 				## Skip cache store
					if ( preg_match( '/' . $regex . '/i', $key ) ) { 	## Use regex for deleteAll
						$deleted[] = $key;
						$this->delete( $key );
					}

				}

			}

			return $deleted;
			// END deleteAll

		}


		/* Small function to convert keys to proper values supported by all caching modes
		** @param cache key name
		==================================================================================================================== */
		function parseKey($key) {
			return str_replace( array(' '), '_', $this->opts['prefix'] . $key );
		}


		/* Clear whole cache including cache store, use with caution
		** @param none
		==================================================================================================================== */
		public function flush() {
			$tmp = $this->garbage( 'clean' );
			$this->quit();
			return $tmp;
		}



		/* Function to save cache_store at end of the request or when we're closing cache
		** @param none
		==================================================================================================================== */
		public function quit() {

			// Save cache_store
			if ( is_array( $this->store ) )
				$this->set( 'cache_store', $this->store, 0 );

		}


		/* Static function that reads cache.
		** @param key to read
		** @param object cache options
		==================================================================================================================== */
		static public function read( $name, $options = array() ) {
			$var = new self( $options );
			return $var->get( $name );
		}

	}

?>