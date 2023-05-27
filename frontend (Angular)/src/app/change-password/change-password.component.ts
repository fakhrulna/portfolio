import { Component, OnInit } from "@angular/core";
import { FormBuilder, FormGroup, Validators } from "@angular/forms";
import { Router } from "@angular/router";
import Swal from "sweetalert2";
import { ApiService } from "../api.service";

@Component({
  selector: "app-change-password",
  templateUrl: "./change-password.component.html",
  styleUrls: ["./change-password.component.css"],
})
export class ChangePasswordComponent implements OnInit {
  status: any;
  checklogin: any;
  public changePasswordForm: FormGroup;
  public bankCode: string;
  ipAddress;
  datalog = {};
  message = "";
  error = false;
  username = '';

  constructor(
    private api: ApiService,
    private fb: FormBuilder,
    private router: Router
  ) {
    console.log("login loaded..");
  }

  ngOnInit() {
    this.username = localStorage.getItem('user_id');
    this.createChangePasswordForm();
  }

  ngOnDestroy() {
    document.body.style.backgroundImage = "";
  }

  createChangePasswordForm() {
    this.changePasswordForm = this.fb.group({
      old_password: ["", [Validators.required]],
      new_password: ["", [Validators.required, Validators.minLength(6)]],
      confirm_password: ["", [Validators.required, Validators.minLength(6)]],
    });
  }

  get old_password() {
    return this.changePasswordForm.get("old_password");
  }
  get new_password() {
    return this.changePasswordForm.get("new_password");
  }
  get confirm_password() {
    return this.changePasswordForm.get("confirm_password");
  }

  changePassword() {
    let data = {
      username: this.username,
      old_password: this.changePasswordForm.value.old_password,
      new_password: this.changePasswordForm.value.new_password,
      confirm_password: this.changePasswordForm.value.confirm_password,
    };

    if (this.changePasswordForm.value.old_password) {
      this.api.changePassword(data).subscribe(
        (data: any) => {
            Swal.fire({
              title: "Success",
              text: "Password Updated",
              icon: "success",
            });
            this.router.navigate(['/home']);
        },
        (err) => {
          Swal.fire({
            title: "Error",
            text: err.error.errors,
          });
        },
        () => console.log("password uploaded (done)")
      );
    } else {
      Swal.fire({
        title: "Error",
        text: "All Field required",
      });
    }
  }
}
