<!-- success question -->
@if(Session::has('success'))
<script>
    const Success = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Success.fire({
        icon: "success",
        title: "{{Session::pull('success')}}"
    });
</script>
{{ Session::forget('success') }}
@endif

<!-- error question -->
@if(Session::has('error'))
<script>
    const Error = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Error.fire({
        icon: "error",
        title: "{{Session::get('error')}}"
    });
</script>
{{ Session::forget('error')}}
@endif

<!-- info question -->
@if(Session::has('info'))
<script>
    const Info = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Info.fire({
        icon: "info",
        title: "{{Session::get('info')}}"
    });
</script>
{{ Session::forget('info')}}
@endif

<!-- alert question -->
@if(Session::has('question'))
<script>
    const Question = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Question.fire({
        icon: "question",
        title: "{{Session::get('question')}}"
    });
</script>
{{ Session::forget('question')}}
@endif

@if(Session::has('warning'))
<script>
    const Warning = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Warning.fire({
        icon: "warning",
        title: "{{Session::get('warning')}}"
    });
</script>
{{ Session::forget('warning')}}
@endif