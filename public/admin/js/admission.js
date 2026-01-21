$(document).ready(function () {
    //ajax
    $("#dynamicProg").hide();
    //for new application
    $("#regcampusId").change(function () {
        var campusId = $("#regcampusId").val();
        $("#mainPrograms").empty();
        if (campusId == "") {
            alert("Select Campus");
            $("#dynamicProg").hide();
        } else {
            $("#dynamicProg").show();

            $.ajax({
                type: "get",
                url: "getmainprograms",
                data: {
                    campusId: campusId,
                },
                success: function (response) {
                    $("#mainPrograms").append(
                        '<option value="">Select Program *</option>',
                    );

                    $.each(response, function (key, value) {
                        $("#mainPrograms").append(
                            '<option value="' +
                                value["id"] +
                                '">' +
                                value["name"] +
                                "</option>",
                        );
                    });
                },
                error: function () {},
            });
        }
    });

    document.querySelectorAll(".toggle-password").forEach((icon) => {
        icon.addEventListener("click", function () {
            const input = document.getElementById(this.dataset.target);

            if (input.type === "password") {
                input.type = "text";
                this.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                this.classList.replace("fa-eye-slash", "fa-eye");
            }
        });
    });

    $("#refresh-captcha").click(function () {
        $.ajax({
            type: "GET",
            url: "captcha-refresh",
            success: function (data) {
                // Find the image inside the span and update its src
                $(".captcha-image img").attr("src", data.captcha);
            },
            error: function (err) {
                console.log("Error refreshing captcha");
            },
        });
    });
});
