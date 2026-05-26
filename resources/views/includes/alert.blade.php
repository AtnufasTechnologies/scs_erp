<style>
    .my-progress-bar {
        background: #fff !important;
        /* Change this hex code to your preferred color */
    }
</style>
<!-- success question -->
@if(Session::has('success'))
<script>
    const Success = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            timerProgressBar: 'my-progress-bar'
        },

        color: "#ffffff", // Text color
        background: "#34ae50", // Background color (e.g., success green)
        iconColor: "#ffffff",
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
    const Errors = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            timerProgressBar: 'my-progress-bar'
        },
        color: "#ffffff", // Text color
        background: "#dc542b", // Background color (e.g., error reg)
        iconColor: "#ffffff",
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Errors.fire({
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
        customClass: {
            timerProgressBar: 'my-progress-bar'
        },
        color: "#353535", // Text color
        background: "#2bdcd6", // Background color (e.g., error reg)
        iconColor: "#353535",
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
        customClass: {
            timerProgressBar: 'my-progress-bar'
        },
        color: "#ffffff", // Text color
        background: "#3f4459", // Background color (e.g., error reg)
        iconColor: "#ffffff",
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
        customClass: {
            timerProgressBar: 'my-progress-bar'
        },
        color: "#454343", // Text color
        background: "#f2df4b", // Background color (e.g., error reg)
        iconColor: "#454343",
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