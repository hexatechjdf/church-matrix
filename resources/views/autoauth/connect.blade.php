@extends('autoauth.public')
@section('title', 'Connecting')
@section('js')
    <script>
        var parentWindow = window.parent;
        window.addEventListener("message", (e) => {
            var data = e.data;
            console.log(data);
            if (data.type == 'location') {
                checkForauth(data);
            }
        });

        $(document).ready(function() {

            let params = new URLSearchParams(location.search);
            let dt = {
                location: params.get('location_id') || "",
                token: params.get('sessionkey') || "",
                web: params.get('web') || "",
            }

            if ((dt.token ?? "") != "" && (dt.location ?? "") != "") {
                checkForauth(dt);
            }
        });

        function checkForauth(dt) {
            Swal.fire({
                title: "Authenticating...",
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            var url = "{{ route('auth.checking') }}";
            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    location: dt.location,
                    token: dt.token,
                    type: dt.web
                },
                success: function(data) {

                    if (data.is_crm == true) {
                        Swal.close();
                        localStorage.setItem('token-id', data.token_id);
                        toastr.success("Location connected successfully!");
                        location.href = data.route + "?v=" + new Date().getTime();
                    } else {
                        Swal.fire({
                            title: "Unable to auth user"
                        })
                    }

                },
                error: function(data) {

                    Swal.fire({
                        title: "Unable to auth user"
                    })

                },
                complete: function() {
                    console.log("completion : " + data);

                }
            });
        }
    </script>
@endsection
