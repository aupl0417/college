/*
 *  Document   : formsWizard.js
 *  Author     : pixelcave
 */
var FormsWizard = function() {
	return {
		init: function() {
			$("#progress-wizard").formwizard({
				focusFirstInput: !0,
				disableUIStyles: !0,
				inDuration: 0,
				outDuration: 0
			});
			var r = $("#progress-bar-wizard");
			r.css("width", "33%").attr("aria-valuenow", "33"),
			$("#progress-wizard").bind("step_shown",
			function(s, e) {
				"progress-first" === e.currentStep ? r.css("width", "33%").attr("aria-valuenow", "33").removeClass("progress-bar-warning progress-bar-success").addClass("progress-bar-danger") : "progress-second" === e.currentStep ? r.css("width", "66%").attr("aria-valuenow", "66").removeClass("progress-bar-danger progress-bar-success").addClass("progress-bar-warning") : "progress-third" === e.currentStep && r.css("width", "100%").attr("aria-valuenow", "100").removeClass("progress-bar-danger progress-bar-warning").addClass("progress-bar-success")
			}),
			$("#basic-wizard").formwizard({
				disableUIStyles: !0,
				inDuration: 0,
				outDuration: 0
			}),
			$("#advanced-wizard").formwizard({
				disableUIStyles: !0,
				validationEnabled: !0,
				validationOptions: {
					errorClass: "help-block animation-slideDown",
					errorElement: "span",
					errorPlacement: function(r, s) {
						s.parents(".form-group > div").append(r)
					},
					highlight: function(r) {
						$(r).closest(".form-group").removeClass("has-success has-error").addClass("has-error"),
						$(r).closest(".help-block").remove()
					},
					success: function(r) {
						r.closest(".form-group").removeClass("has-success has-error"),
						r.closest(".help-block").remove()
					},
					rules: {
						val_username: {
							required: !0,
							minlength: 2
						},
						val_password: {
							required: !0,
							minlength: 5
						},
						val_confirm_password: {
							required: !0,
							equalTo: "#val_password"
						},
						val_email: {
							required: !0,
							email: !0
						},
						val_terms: {
							required: !0
						}
					},
					messages: {
						val_username: {
							required: "Please enter a username",
							minlength: "Your username must consist of at least 2 characters"
						},
						val_password: {
							required: "Please provide a password",
							minlength: "Your password must be at least 5 characters long"
						},
						val_confirm_password: {
							required: "Please provide a password",
							minlength: "Your password must be at least 5 characters long",
							equalTo: "Please enter the same password as above"
						},
						val_email: "Please enter a valid email address",
						val_terms: "Please accept the terms to continue"
					}
				},
				inDuration: 0,
				outDuration: 0
			});
			var s = $("#clickable-wizard");
			s.formwizard({
				disableUIStyles: !0,
				inDuration: 0,
				outDuration: 0
			}),
			$(".clickable-steps a").on("click",
			function() {
				var r = $(this).data("gotostep");
				s.formwizard("show", r)
			})
		}
	}
} ();