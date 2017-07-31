# upload_image
PHP Upload Image Class

Created by Sakarioka.com.
Email: gurinlife@gmail.com / saka@sakarioka.com.
Class function: Validate and upload image file using PHP.

Simply just need to add the class and run the upload function.
The uploaded image will be moved into uploaded directory.

Example: 
$c_image = new Upload_Image($_FILES['image']);
$c_image->upload();
