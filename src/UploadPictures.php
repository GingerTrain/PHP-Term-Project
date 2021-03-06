
<?php
session_start();
include 'helpers/util.php';
include_once ("ConstantsAndSettings.php");
include 'helpers/protected.php';
ValidateUser();
include 'helpers/validation.php';

include 'helpers/albums.php';
include 'helpers/pictures.php';
include 'helpers/picturefunctions.php';
include 'shared/db.php';
$owner = $_SESSION['login'];
$imgTitle = getPostSafely('imageTitle');
$description = getPostSafely('description');
$albums = getAlbumsByUser($owner, $db);
// TODO Parse Images
$error = '';
if (isset($_POST['submit'])) {
  $albumId = $_POST['albumId'];
  for ($j = 0; $j < count($_FILES['txtUpload']['name']); $j++) {
    if ($_FILES['txtUpload']['error'][$j] == 0) {
        $filePath = save_uploaded_file(ORIGINAL_PICTURES_DIR, $j);
        $imageDetails = getimagesize($filePath);
        if ($imageDetails && in_array($imageDetails[2], $supportedImageTypes)) {
            resamplePicture($filePath, ALBUM_PICTURES_DIR, IMAGE_MAX_WIDTH, IMAGE_MAX_HEIGHT);
            resamplePicture($filePath, ALBUM_THUMBNAILS_DIR, THUMB_MAX_WIDTH, THUMB_MAX_HEIGHT);
            $pathInfo = pathinfo($filePath);
            $fileName = $pathInfo['basename'];
            addPicture($db, $fileName, $imgTitle, $description, $albumId );
        } else {
            $error = "Uploaded file is not a supported type";
            unlink($filePath);
        }
    } elseif ($_FILES['txtUpload']['error'][$j] == 1) {
        $error = "Upload file is too large";
    } elseif ($_FILES['txtUpload']['error'][$j] == 4) {
        $error = "No upload file specified";
    } else {
        $error = "Error happened while uploading the file. Try again later";
    }
  // header("Location: UploadPictures.php");
  // exit();
  }
}
include 'shared/header.php';
// var_dump($_POST);
// var_dump($_FILES);
?>

<div class="section hero is-fullheight">
  <div class="container">
  <h1 class="title is-1 has-text-centered">Upload Pictures</h1>
      <?php  include 'shared/welcome.php' ;?>
    <div class="column is-7 is-offset-2 has-text-left">

      <form
        action="<?php echo $_SERVER['PHP_SELF']?>"
        method="POST"
        class="inputForm"
        enctype="multipart/form-data"
        >

      <div class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">Title</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <p class="control is-expanded has-icons-left has-icons-right">
                      <input
                      class="input"
                      type="text"
                      placeholder=""
                      name="imageTitle"
                      value = "<?php echo (isset($albumTitle))?$albumTitle:'';?>"
                      >
                      <span class="icon is-small is-right" style="display:none">
                      </span>
                      <span class="icon is-small is-left">
                        <i class="fas fa-images"></i>
                      </span>
                      <!-- <p class="help is-danger"></p> -->
                    </p>
                  </div>
                </div>
              </div>
              <div class="field is-horizontal ">
                  <div class="field-label is-normal">
                    <label class="label">Files</label>
                  </div>
                  <div class="field-body">
                      <div id="files" class="file has-name is-fullwidth" >
                        <label class="file-label">
                          <input 
                              type="file" 
                              class="file-input" 
                              name="txtUpload[]"
                              multiple
                              >
                              <!-- accept="image/png, image/jpeg, image/gif"      -->
                          <span class="file-cta">
                            <span class="file-icon">
                              <i class="fas fa-upload"></i>
                            </span>
                            <span class="file-label file-text">
                              Upload Images
                            </span>
                          </span>
                        </label>
                      </div>
                  </div>
              </div>
        <div class="field is-horizontal">
            <div class="field-label ">
                <label class="label">Upload to</label>
            </div>
            <div class="field-body">
              <div class="is-fullwidth control has-icons-left">
                <div class="select is-fullwidth">
                  <select name="albumId">
                    <?php
                      foreach($albums as $album) {
                        echo "<option value='$album->Album_Id'> $album->Title </option>";
                      }
                    ?>
                  </select>
                </div>
                <div class="icon is-small is-left">
                  <i class="fas fa-images"></i>
                </div>
              </div>
            </div>
        </div>

      <!-- DESCRIPTION -->
      <div class="field is-horizontal">
          <div class="field-label ">
            <label class="label">Description</label>
          </div>
          <div class="field-body ">
            <div class="field">
                <div class="control">
                  <textarea class="textarea is-primary" name="description" placeholder="enter description"></textarea>
                </div>
              </div>
          </div>
        </div>

        <div class="field-body">
                <div class="field">
                  <div class="control">
                  </div>
                </div>
                    <div class="control">
                    <input
                      class="button is-success"
                      type="submit" value="AddPictures" name="submit" >
                      <input class="button is-warning clearButton"
                       type="reset"
                       name="reset"
                       onclick="location.href='UploadPictures.php'; " value="Reset">
                    </div>
                  </div>
              </div>
      </form>
    </div>  <!-- COLUMN -->
  </div>    <!-- CONTAINER -->
</div>      <!-- HERO -->

<script>
let uploadImg = document.querySelector('.file-input');
let fileLabel = document.querySelector('.file-text');
const fileBtn = document.querySelector('#files');
uploadImg.addEventListener('change', (e) => {
      let text = 'Choose a File';
      const files = Array.from(e.target.files);
      files.length !== 0 ? text = files[0].name : 'Upload Images...';
      fileLabel.innerHTML = text;
      fileBtn.classList.add('is-success');
});
</script>
<?php include 'shared/footer.php'; ?>