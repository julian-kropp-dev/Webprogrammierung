<?php

namespace model\posts;

define("JPG", 2);
define("PNG", 3);

class ImageUploadHandler
{

     function setupImage($filenamePath, $imgType, $tempLocation) : bool {

        // Überprüfen, ob die Datei erfolgreich hochgeladen wurde
        if (move_uploaded_file($tempLocation, $filenamePath)) {

            if ($imgType == PNG){
                $source = imagecreatefrompng($filenamePath);
            }else {
                $source = imagecreatefromjpeg($filenamePath);
            }

            // Abmessungen des Originalbildes
            $source_width = imagesx($source);
            $source_height = imagesy($source);

            // Mindestgröße für das Zuschneiden
            $target_size = min($source_width, $source_height);

            // Erstelle ein neues Zielbild mit der gewünschten Größe (1:1)
            $target = imagecreatetruecolor($target_size, $target_size);

            // Schneide das Originalbild in das Zielbild (zentriert zuschneiden)
            $source_x = ($source_width - $target_size) / 2;
            $source_y = ($source_height - $target_size) / 2;
            imagecopy($target, $source, 0, 0, $source_x, $source_y, $target_size, $target_size);

            // Speichere das Zielbild
            if ($imgType == PNG){
                imagepng($target, $filenamePath);
            }else {
                imagejpeg($target, $filenamePath);
            }

            // Freigabe des Speicherplatzes
            imagedestroy($source);
            imagedestroy($target);



            return true;
        } else {
            return false;
        }

    }

}