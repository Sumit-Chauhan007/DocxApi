<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="mt-4 ms-4 col-xl-6 ">

                <input class="form-control mb-4" type="file" name="" id="document" accept=" application/pdf">
                <button type="button" onclick="postDocument()" value="" class="btn btn-primary">Submit</button>

            </div>
            <div align="right" class="col-xl-6">
                <p class="error" style="color: red">
                </p>
            </div>
            <div class="col-xl-6">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Document Id</th>
                            <th scope="col">Document Id Creation Date</th>
                        </tr>
                    </thead>
                    <tbody  class="docxBody">   
                        @foreach ($docx as $doc)
                        <tr >
                            <td>{{ $id }}</td>
                            <td>{{ $doc->document_Id }}</td>
                            <td>{{ $doc->created_at }}</td>
                        </tr>
                        <?php $id++?> 
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <button onclick="ask()">hi</button>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous">
    </script>
    <script>
        function ask(){
            $.ajax({
                type: "post",
                url: "{{ url('/ask-question') }}",
                contentType: 'multipart/form-data',
                cache: false,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    
                }
            });
        }
        function postDocument() {
            var document = $('#document').prop('files')[0];
            var formData = new FormData();
            formData.append('document', document)
            $.ajax({
                type: "post",
                url: "{{ url('/upload-document') }}",
                contentType: 'multipart/form-data',
                cache: false,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                success: function(response) {
                    if (response.error) {
                        $('.error').html(response.error);
                    } else {
                        $('.error').html(" ");
                        $('.docxBody').html(response.html);
                    }
                }
            });
        }
    </script>
</body>

</html>
