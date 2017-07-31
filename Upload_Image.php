<?php

/**
 *
 * Created by Sakarioka.com
 * Email: gurinlife@gmail.com / saka@sakarioka.com
 * Class function: Validate and upload image file using PHP
 *
 */

class Upload_Image
{
  protected $uploaded_dir      = null;
  protected $image             = null;
  protected $image_name        = null;
  protected $image_extension   = null;
  protected $image_errors      = null;
  protected $allowed_extension = array('jpg', 'gif', 'png');

  /**
   *
   * Function __construct
   * Set image file data and uploaded directory.
   * Do first process to do before class can be used.
   *
   */
  public function __construct($image, $uploaded_dir = null)
  {
    if ($image['error'] == 0) {
      if (empty($uploaded_dir)) {
        $this->set_uploaded_dir('upload');
      } else {
        $this->set_uploaded_dir($uploaded_dir);
      }

      $this->image = $image;
      $this->set_image_name($this->generate_random_name());

      if ($image_extension = $this->generate_image_extension()) {
        if (!in_array($image_extension, $this->allowed_extension)) {
          $this->set_image_errors('The uploaded image extension is not allowed. Only '.implode(', ', $this->allowed_extension).' allowed.');
        } else {
          $this->set_image_extension($image_extension);
        }
      } else {
        $this->set_image_errors('The uploaded file image extension not found.');
      }
    } else {
      $this->set_image_errors($this->validate_errors($image['error']));
    }
  }

  public function set_uploaded_dir($location)
  {
    $location = $_SERVER['DOCUMENT_ROOT'].'/'.ltrim($location, '/');

    if (!file_exists($location)) {
      mkdir($location, 0777, true);
    }

    $this->uploaded_dir = $location;
  }

  public function set_image_name($image_name)
  {
    $this->image_name = $image_name;
  }

  public function set_image_extension($image_extension)
  {
    $this->image_extension = $image_extension;
  }

  public function set_image_errors($image_errors)
  {
    $this->image_errors = $image_errors;
  }

  public function get_image_errors()
  {
    return $this->image_errors;
  }

  /**
   *
   * Function validate errors.
   * Return string of error from upload status.
   *
   */
  public function validate_errors($image_errors)
  {
    switch ($image_errors) {
      case 'UPLOAD_ERR_INI_SIZE': return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
      case 'UPLOAD_ERR_FORM_SIZE': return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
      case 'UPLOAD_ERR_PARTIAL': return 'The uploaded file was only partially uploaded.';
      case 'UPLOAD_ERR_NO_FILE': return 'No file was uploaded.';
      case 'UPLOAD_ERR_NO_TMP_DIR': return 'Missing a temporary folder. Introduced in PHP 5.0.3.';
      case 'UPLOAD_ERR_CANT_WRITE': return 'Failed to write file to disk. Introduced in PHP 5.1.0.';
      case 'UPLOAD_ERR_EXTENSION': return 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced in PHP 5.2.0.';
      default: return 'Unknown upload errors.';

    }
  }

  /**
   *
   * Function generate image extension
   * Generate image extension depend on the data of the image file
   *
   */
  public function generate_image_extension()
  {
    if (isset($this->image['data'])) {
      $data = $this->image['data'];
    } elseif (isset($this->image['tmp_name'])) {
      $data = file_get_contents($this->image['tmp_name']);
    }

    if (strncmp("\xff\xd8", $data, 2) === 0) {
      return 'jpg';
    } elseif (preg_match('/^GIF8[79]a/', $data) === 1) {
      return 'gif';
    } elseif (strncmp("\x89PNG\x0d\x0a\x1a\x0a", $data, 8) === 0) {
      return 'png';
    } elseif (strncmp("BM", $data, 2) === 0) {
      return 'bitmap';
    } elseif (strncmp("\x00\x00\x01\x00", $data, 4) === 0) {
      return 'ico';
    } elseif (strncmp("\x49\x49\x2a\x00\x08\x00\x00\x00", $data, 8) === 0 ||
              strncmp("\x4d\x4d\x00\x2a\x00\x00\x00\x08", $data, 8) === 0) {
      return 'tiff';
    }
  }

  /**
   *
   * Function generate random name.
   * Generate random name image for the image file.
   * Using date now and random string name.
   *
   */
  public function generate_random_name($name_length = 30)
  {
    $characters        = '0123456789abcdefghijklmnopqrstuvwxyz';
    $characters_length = strlen($characters);
    $random_name       = '';

    for ($i = 0; $i < $name_length; $i++) {
      $random_name .= $characters[rand(0, $characters_length - 1)];
    }

    $random_name = strtotime('now').'.'.$random_name;

    if (file_exists($this->uploaded_dir.$random_name.'.'.$this->image_extension)) {
      return $this->generate_random_name();
    }

    return $random_name;
  }

  /**
   *
   * Function upload.
   * Last function to be called before the uploaded file moved into uploaded directory.
   * Return false when something wrong.
   *
   */
  public function upload()
  {
    dump($this);
    if (empty($this->get_image_errors())) {
      return move_uploaded_file($this->image['tmp_name'], "{$this->uploaded_dir}/{$this->image_name}.{$this->image_extension}");
    } else {
      return false;
    }
  }
}