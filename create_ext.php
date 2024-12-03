<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Extension Form</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-color: #f8f9fa;
    }
    .container {
      max-width: 500px;
      margin-top: 20px;
    }
    .card-header {
      font-size: 1.5rem;
      font-weight: bold;
      color: #343a40;
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="card shadow">
      <div class="card-header text-center bg-primary text-white">
        Create Extension
      </div>
      <div class="card-body">
        <form id ="myForm">
          <!-- Extension Field -->
          <div class="mb-3">
            <label for="extension" class="form-label">Extension</label>
            <input type="number" class="form-control" id="extension" name="extension" placeholder="Enter extension" onblur="validateDupExt(this.value);">
            <small class="form-text text-danger" id ="extErr"></small>
          </div>

          <!-- Username Field -->
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
          </div>

          <!-- Password Field -->
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
          </div>

          <!-- Caller ID Field -->
          <div class="mb-3">
            <label for="callerId" class="form-label">Caller ID</label>
            <input type="text" class="form-control" id="callerId" name="callerId" placeholder="Enter caller ID" required>
          </div>

          <!-- Call Recording Field -->
          <div class="mb-3">
            <label for="callrc" class="form-label">Call Recording</label>
           <select class = "form-control" id = "call_recording" name = "call_recording">
              <option value = "yes">Enable</option>
              <option value = "no">Disable</option>
           </select>
          </div>

          <!-- Submit Button -->
          <div class="text-center">
            <button type="button" onclick="createExt()" class="btn btn-primary" id ="create-ext">Create Extension</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>

    function validateDupExt(ext) {
      console.warn(ext);
      var createBtn = document.getElementById('create-ext');
      $.ajax({
        url: 'add_ext.php',
        method: 'POST',
        data: {'extension': ext, 'validateExt': 'validateExt'},
        success: function(response) {
          var jsonResponse = JSON.parse(response);
          var extErr = document.getElementById('extErr');
          extErr.innerHTML = '';
          // console.warn(jsonResponse.success)
          if(jsonResponse.success) {
            extErr.innerHTML = 'Extension already exist';
            createBtn.disabled = true;
          } else {
            extErr.innerHTML = '';
            createBtn.disabled = false;
          }
        },
        error: function(xhr, status, error) {
              // Handle the error response here
          console.error('AJAX Error:', status, error);
        }
      })
    }

    function createExt() {
      var ext = document.getElementById('extension').value;
      var password = document.getElementById('password').value;
      var username = document.getElementById('username').value;
      var caller_id = document.getElementById('callerId').value;
      // var context = document.getElementById('context').value;
      var callRec = document.getElementById('call_recording').value;
      var createBtn = document.getElementById('create-ext');

      // debugger;
      var frm = new FormData();
      frm.append('extension', ext );
      frm.append('username', username);
      frm.append('password', password);
      frm.append('caller_id', caller_id);
      frm.append('createExt', 'createExt');
      frm.append('call_recording', callRec);

      createBtn.innerHTML = "<img src = '../images/search_loader.gif' style = 'width:30px;height:30px;' />";

      $.ajax({
          url: 'add_ext.php',
          method: 'POST',
          data: frm,
          contentType: false, 
          processData: false, 
          success: function(response) {

            var jsonResponse = JSON.parse(response);
            console.warn(jsonResponse)
            console.error(jsonResponse.success)
              if(jsonResponse.success) {
                Swal.fire({
                title: "Status",
                text: "Extension created successfully!",
                icon: "success"
              });

              setTimeout(function() {
                  window.location.href = 'extensions.php';
              }, 2000);
              
              createBtn.innerHTML = 'Create Extension';
              // document.getElementById("myForm").reset();
            } else {
              Swal.fire({
                title: "Oops..",
                text: "Something went wrong!",
                icon: "error"
              });

              createBtn.innerHTML = 'Create Extension';
            }
          },
          error: function(xhr, status, error) {
              // Handle the error response here
              console.error('AJAX Error:', status, error);
          }
    });
    }
  </script>
</body>
</html>
