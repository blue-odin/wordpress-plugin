<?php

if ( ! function_exists('blueodin_write_log')) {
   function blueodin_write_log ( $message, $data )  {
      if ( is_array( $data ) || is_object( $data ) ) {
         error_log( $message . ": " . json_encode( $data, JSON_PRETTY_PRINT ) );
      } else {
         error_log( "$message: $data" );
      }
   }
}

