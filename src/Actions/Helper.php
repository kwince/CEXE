<?php

namespace Cexe\Actions;

class Helper{

    public function __construct(){
      ;
    }
    function do( $arr ){
      if (DIRECTORY_SEPARATOR === '\\' ){
        $p=popen('cls', 'w');
        pclose($p);
      }  else {
        echo chr(27).chr(91).'H'.chr(27).chr(91).'J'; //clears screen
      }

      echo(
"The following options are available:\n".
"\n".
"\t(This help screen)\n".
"\t-H\n\n".

"\t(Make Key File)\n".
"\t-K FileNameForDestination\n\n".

"\t(Check validity of Key File)\n".
"\t-C HashFileOfKeyToBeTested  KeyFileMadeFromThisProgram\n\n".

"\t(Pack and encrypt SOURCE directory into DESTINATION directory)\n".
"\t-P DIRECTORYNameForSource  DIRECTORYNAMEForArchiveDestination FILENameForKeyFileMadeFromThisProgram  Password  optional_hashOfKey\n".
"\t-------->(NOTE: DO NOT RUN IN SAME DIRECTORY THAT YOU ARE PACKING\n".
"\t-------->(NOTE: MAKE SURE YOU HAVE WRITE PERMISSIONS IN DIRECTORY RUN FROM AND DIRECTOY OF DESTINATION\n\n".

"\t(Unpack and decrypt SOURCE file into DESTINATION directory)\n".
"\t-U DIRECTORYNameForDestination  FILENameForSource  FILENameForKeyFileMadeFromThisProgram  Password\n\n\n".


"NOTE ON PASSWORDS: \n".
"\t1/ They must be at least 20 characters long.\n".
"\t2/ They must contain at least one letter, one number, and one special character. Legal special characters are '~@#_^*%/.+:;='\n".
"\t     Spaces and control characters are not allowed.\n".
"\t3/ It is recommended to use a 'Dice passphrase' in combination with a few numbers, special characters, and CapitaLizations.\n".
"\t      For example: 867JJwcitt5309.harvest.carefully.favorably.march=bonanza\n".
"\t4/ See https://www.eff.org/dice\n".
"\t   and https://www.rempe.us/diceware/#eff\n".
"\t   and https://www.ibm.com/docs/hr/sgklm/3.0.1?topic=policy-supported-special-characters-in-passwords\n".
"\t   and https://theworld.com/%7Ereinhold/dicewarefaq.html#memory\n".
"\t/5 Bury a copy of it where no else would look, preferably accessibly remote,\n".
"\t   not accidentally discoverable.\n\n\n" .


"NOTE ON KEYS AND KEY HASHES: \n".
"\t1/ \n".
"\t2/ \n".
"\t   \n".
"\t3/ \n".
"\t   \n"
);
    }
}
?>
