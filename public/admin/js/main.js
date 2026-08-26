$(function () {
    const MODE_CLASSES = "light-theme dark-theme semi-dark";
    const SIDEBAR_COLOR_CLASSES =
        "sidebarcolor1 sidebarcolor2 sidebarcolor3 sidebarcolor4 sidebarcolor5 sidebarcolor6 sidebarcolor7 sidebarcolor8";

    function setModeClass(modeClass) {
        $("html").removeClass(MODE_CLASSES).addClass(modeClass);
    }

    function setSidebarColorClass(sidebarClass) {
        $("html")
            .removeClass(SIDEBAR_COLOR_CLASSES)
            .addClass("semi-dark color-sidebar " + sidebarClass);
    }

    $(function () {
        $("#menu").metisMenu();
    });

    $(".nav-toggle-icon").on("click", function () {
        $(".wrapper").toggleClass("toggled");
    });

    $(".mobile-menu-button").on("click", function () {
        $(".wrapper").addClass("toggled");
    });

    $(function () {
        for (
            var e = window.location,
                o = $(".metismenu li a")
                    .filter(function () {
                        return this.href == e;
                    })
                    .addClass("")
                    .parent()
                    .addClass("mm-active");
            o.is("li");
        )
            o = o
                .parent("")
                .addClass("mm-show")
                .parent("")
                .addClass("mm-active");
    });

    $(".toggle-icon").click(function () {
        $(".wrapper").hasClass("toggled")
            ? ($(".wrapper").removeClass("toggled"),
              $(".sidebar-wrapper").unbind("hover"))
            : ($(".wrapper").addClass("toggled"),
              $(".sidebar-wrapper").hover(
                  function () {
                      $(".wrapper").addClass("sidebar-hovered");
                  },
                  function () {
                      $(".wrapper").removeClass("sidebar-hovered");
                  },
              ));
    });

    ($(".btn-mobile-filter").on("click", function () {
        $(".filter-sidebar").removeClass("d-none");
    }),
        $(".btn-mobile-filter-close").on("click", function () {
            $(".filter-sidebar").addClass("d-none");
        }),
        $(".mobile-search-button").on("click", function () {
            $(".searchbar").addClass("full-search-bar");
        }),
        $(".search-close-icon").on("click", function () {
            $(".searchbar").removeClass("full-search-bar");
        }),
        $(document).ready(function () {
            ($(window).on("scroll", function () {
                $(this).scrollTop() > 300
                    ? $(".back-to-top").fadeIn()
                    : $(".back-to-top").fadeOut();
            }),
                $(".back-to-top").on("click", function () {
                    return (
                        $("html, body").animate(
                            {
                                scrollTop: 0,
                            },
                            600,
                        ),
                        !1
                    );
                }));
        }));

    ($(".dark-mode-icon").on("click", function () {
        if ($(".mode-icon ion-icon").attr("name") == "sunny-sharp") {
            $(".mode-icon ion-icon").attr("name", "moon-sharp");
            setModeClass("light-theme");
        } else {
            $(".mode-icon ion-icon").attr("name", "sunny-sharp");
            setModeClass("dark-theme");
        }
    }),
        // Theme switcher

        $("#LightTheme").on("click", function () {
            setModeClass("light-theme");
        }),
        $("#DarkTheme").on("click", function () {
            setModeClass("dark-theme");
        }),
        $("#SemiDark").on("click", function () {
            setModeClass("semi-dark");
        }),
        // headercolor colors

        $("#headercolor1").on("click", function () {
            ($("html").addClass("color-header headercolor1"),
                $("html").removeClass(
                    "headercolor2 headercolor3 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8",
                ));
        }),
        $("#headercolor2").on("click", function () {
            ($("html").addClass("color-header headercolor2"),
                $("html").removeClass(
                    "headercolor1 headercolor3 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8",
                ));
        }),
        $("#headercolor3").on("click", function () {
            ($("html").addClass("color-header headercolor3"),
                $("html").removeClass(
                    "headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8",
                ));
        }),
        $("#headercolor4").on("click", function () {
            ($("html").addClass("color-header headercolor4"),
                $("html").removeClass(
                    "headercolor1 headercolor2 headercolor3 headercolor5 headercolor6 headercolor7 headercolor8",
                ));
        }),
        $("#headercolor5").on("click", function () {
            ($("html").addClass("color-header headercolor5"),
                $("html").removeClass(
                    "headercolor1 headercolor2 headercolor4 headercolor3 headercolor6 headercolor7 headercolor8",
                ));
        }),
        $("#headercolor6").on("click", function () {
            ($("html").addClass("color-header headercolor6"),
                $("html").removeClass(
                    "headercolor1 headercolor2 headercolor4 headercolor5 headercolor3 headercolor7 headercolor8",
                ));
        }),
        $("#headercolor7").on("click", function () {
            ($("html").addClass("color-header headercolor7"),
                $("html").removeClass(
                    "headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor3 headercolor8",
                ));
        }),
        $("#headercolor8").on("click", function () {
            ($("html").addClass("color-header headercolor8"),
                $("html").removeClass(
                    "headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor7 headercolor3",
                ));
        }));

    // sidebar colors
    $("#sidebarcolor1").click(theme1);
    $("#sidebarcolor2").click(theme2);
    $("#sidebarcolor3").click(theme3);
    $("#sidebarcolor4").click(theme4);
    $("#sidebarcolor5").click(theme5);
    $("#sidebarcolor6").click(theme6);
    $("#sidebarcolor7").click(theme7);
    $("#sidebarcolor8").click(theme8);

    function theme1() {
        setSidebarColorClass("sidebarcolor1");
    }

    function theme2() {
        setSidebarColorClass("sidebarcolor2");
    }

    function theme3() {
        setSidebarColorClass("sidebarcolor3");
    }

    function theme4() {
        setSidebarColorClass("sidebarcolor4");
    }

    function theme5() {
        setSidebarColorClass("sidebarcolor5");
    }

    function theme6() {
        setSidebarColorClass("sidebarcolor6");
    }

    function theme7() {
        setSidebarColorClass("sidebarcolor7");
    }

    function theme8() {
        setSidebarColorClass("sidebarcolor8");
    }

    new PerfectScrollbar(".header-notifications-list");

    // Tooltops
    $(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
});
