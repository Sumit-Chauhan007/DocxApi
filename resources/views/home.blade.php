<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <style>
        .edify_loader {
            position: fixed;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            top: 0;
            left: 0;
            background: linear-gradient(106.45deg, rgba(65, 70, 151, 0.8) 2.5%, rgba(0, 169, 145, 0.8) 98.82%);
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
            z-index: 9999;
        }

        .edify_loader .loader {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: inline-block;
            position: relative;
            border: 3px solid;
            border-color: #FFF #FFF transparent transparent;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
        }

        .edify_loader .loader::after,
        .edify_loader .loader::before {
            content: '';
            box-sizing: border-box;
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            margin: auto;
            border: 3px solid;
            border-color: transparent transparent #414697 #414697;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            box-sizing: border-box;
            animation: rotationBack 0.5s linear infinite;
            transform-origin: center center;
        }

        .edify_loader .loader::before {
            width: 42px;
            height: 42px;
            border-color: #FFF #FFF transparent transparent;
            animation: rotation 1.5s linear infinite;
        }

        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes rotationBack {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(-360deg);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="mt-4 ms-4 col-xl-6">

                <input class="form-control mb-4" type="file" name="" id="document" accept=" application/pdf">
                <button type="button" onclick="postDocument()" value="" class="btn btn-primary">Submit</button>

            </div>
            <div class="col-xl-12">
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
                            <th scope="col">Delete Document</th>
                        </tr>
                    </thead>
                    <tbody class="docxBody">
                        @foreach ($docx as $doc)
                            <tr>
                                <td>{{ $id }}</td>
                                <td>{{ $doc->document_Id }}</td>
                                <td>{{ $doc->created_at }}</td>
                                <td><button onclick="delet('{{ $doc->document_Id }}')"
                                        class="btn btn-primary">Delete</button></td>
                            </tr>
                            <?php $id++; ?>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-xl-6">
                <p class="answer"></p>
            </div>
        </div>
        <div class="ed_loader">
            <span class="loader"></span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous">
    </script>
    <script>

        function delet($id) {
            var formData = new FormData();
            formData.append('id', $id);
            $('.ed_loader').addClass('edify_loader');
            $.ajax({
                type: "post",
                url: "{{ url('/delete-doc') }}",
                contentType: 'multipart/form-data',
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('.answer').html('');
                    $('.ed_loader').removeClass('edify_loader');
                    if (response.error) {
                        $('.error').html(response.error);
                    } else {
                        $('.docxBody').html(response.html);
                    }
                    
                }
            });
        }

        function postDocument() {
            var document = $('#document').prop('files')[0];
            var formData = new FormData();
            formData.append('document', document);
            $('.ed_loader').addClass('edify_loader');
            $.ajax({
                type: "post",
                url: "{{ url('/ask-upload-document') }}",
                contentType: 'multipart/form-data',
                cache: false,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                success: function(response) {
                    $('.answer').html('');
                    $('.ed_loader').removeClass('edify_loader');
                    // if (response.error) {
                    //     $('.error').html(response.error);
                    // } else {
                        $('.error').html(" ");
                        $('.answer').html(response.message);
                    // }
                }
            });
        }
    </script>
</body>

</html>
