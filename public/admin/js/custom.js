$(document).ready(function () {
    var tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    /**Datatable Search and sort */
    new DataTable("#exportTable", {
        layout: {
            topStart: {
                pageLength: 10,
                buttons: ["copy", "excel", "pdf"],
            },
        },
    });

    let table = new DataTable("#myTable");
    //multiselect

    $("#addmorebtn").click(function (e) {
        e.preventDefault();
        $("#morefeehead").clone().appendTo(".modal-body");
    });

    $("#updatemorebtn").click(function (e) {
        e.preventDefault();
        $("#updatefeehead").clone().appendTo(".editfeestructure");
    });

    $(".select-multiple").bsMultiSelect();

    dselect(document.querySelector(".livesearch"), {
        search: true,
        clearable: true,
    });

    document.querySelectorAll(".dselect-example").forEach(function (el) {
        dselect(el, {
            search: true,
            creatable: true,
            clearable: true,
            maxHeight: "300px",
            size: "lg",
        });
    });

    $("#loading").hide();
    // $('[data-toggle="tooltip"]').tooltip();

    $("#selectAll").click(function () {
        var myArray = [];
        $("input[type=checkbox]").prop("checked", $(this).prop("checked"));
        var checkboxes = document.querySelectorAll(
            "input[id=checkboxItem]:checked"
        );
        for (var i = 0; i < checkboxes.length; i++) {
            myArray.push(checkboxes[i].value);
        }
        console.log(myArray);
    });

    $("#exportData").click(function () {
        var myArray = [];
        $("input[type=checkbox]").prop("checked", $(this).prop("checked"));
        var checkboxes = document.querySelectorAll(
            "input[id=checkboxItem]:checked"
        );
        for (var i = 0; i < checkboxes.length; i++) {
            myArray.push(checkboxes[i].value);
        }

        $.ajax({});
    });

    $("#btnclick").click(function () {
        let timerInterval;
        Swal.fire({
            timer: 1000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
                const timer = Swal.getPopup().querySelector("b");
                timerInterval = setInterval(() => {
                    timer.textContent = `${Swal.getTimerLeft()}`;
                }, 100);
            },
            willClose: () => {
                clearInterval(timerInterval);
            },
        }).then((result) => {
            /* Read more about handling dismissals below */
            if (result.dismiss === Swal.DismissReason.timer) {
                console.log("Error in the timer");
            }
        });
    });

    $("#custom-submitBtn").click(function (e) {
        e.preventDefault();
        Swal.fire({
            title: "Do you want to continue?",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                Swal.fire({
                    title: "<div class='spinner-border text-primary'></div>",
                    showConfirmButton: false,
                });
                $("#custom-form-submit").submit();
                $("#custom-submitBtn").hide();
                $("#loading").show();
            }
        });
    });

    ClassicEditor.create(document.querySelector("#editor"), {
        removePlugins: [
            "CKFinderUploadAdapter",
            "CKFinder",
            "EasyImage",
            "Image",
            "ImageCaption",
            "ImageStyle",
            "ImageToolbar",
            "ImageUpload",
            "MediaEmbed",
        ],
    })
        .then((editor) => {
            console.log(editor);
        })
        .catch((error) => {
            console.error(error);
        });
});
