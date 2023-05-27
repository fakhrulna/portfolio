import { Component, OnInit, OnDestroy } from '@angular/core';
import { Router }              from '@angular/router';
import { ApiService } from '../api.service';
import { FormControl, FormBuilder, FormGroup, Validators } from '@angular/forms';
import Swal from 'sweetalert2';
import { HttpClient } from '@angular/common/http';


@Component({
    selector: 'app-login',
    templateUrl: './login.component.html'
})
export class LoginComponent implements OnInit, OnDestroy {
  status: any;
  checklogin: any;
  status_login: boolean;
  public loginForm: FormGroup;
  public bankCode: string;
  ipAddress;
  datalog = {};
    message = '';
  error = false;

  constructor(
    private api: ApiService,
    // public authService: AuthService,
    private fb: FormBuilder,
    private router: Router,
      private http: HttpClient,
  ) {
        console.log('login loaded..')
  }

  ngOnInit() {
      console.log('login created..');
      document.body.className = 'bg-login fixed-left';
    document.body.style.backgroundImage =
      "url('./assets/images/img_background.jpg')";
    this.createLoginForm();
  }

  ngOnDestroy() {
      document.body.style.backgroundImage = '';
  }


  createLoginForm() {
    this.status_login = true;
    this.loginForm = this.fb.group({
        username: ['', [Validators.required]],
        password: ['', [Validators.required, Validators.minLength(6)]]
    });
  }


    get username() { return this.loginForm.get('username'); }
    get password() { return this.loginForm.get('password'); }

  sendLogin() {
    let data = {
        'username': this.loginForm.value.username,
        'password': this.loginForm.value.password,
        'sector': 'HOST',
    };

    if (this.loginForm.value.username) {
        this.api.sendLogin(data)
          .subscribe((data: any) => {
          this.status = data;
          this.api.authDataLogin = data;
          // this.authService.authServiceResult = data;
          // console.log("mameen" + this.authService.authServiceResult);
          if (this.status.status_code == "-1") {
              console.log('Login Invalid');
            Swal.fire({
                title: 'Login Invalid',
              text: this.status.error_description,
            });
            this.status_login = false;
          } else {
            this.status_login = true;
            localStorage.setItem("user_id", this.loginForm.value.username);
            localStorage.setItem("full_name", this.status.full_name);
            localStorage.setItem("access_token", this.status.access_token);
            localStorage.setItem("bank_name", this.status.bank_name);
            localStorage.setItem("bank_code", this.status.bank_code);
            localStorage.setItem("bank_logo", this.status.bank_logo);
            localStorage.setItem("is_gohalal", this.status.is_gohalal);
            localStorage.setItem(
              "user_menu",
              btoa(JSON.stringify(this.status.menu))
            );
            localStorage.setItem("user_roles", this.status.roles);
            Swal.fire({
                title: 'Login success',
              text: "Login success",
                icon: 'success'
            });
            const userId = localStorage.getItem("user_id");
            const fullName = localStorage.getItem("full_name");
            const bankCode = localStorage.getItem("bank_code");
            const accessToken = localStorage.getItem("access_token");
            const bankName = localStorage.getItem("bank_name");
            this.activityLog(
              "login",
              userId,
              fullName,
              bankCode,
              accessToken,
              bankName
            );
            if (this.status.roles == "product") {
              this.router.navigate(["/products"]);
            } else {
              this.router.navigate(["/home"]);
            }
          }
          // console.log('sendLogin (loaded): ' + JSON.stringify(this.status));
        },
          err => console.error(err),
          () => console.log('sendLogin (done)'));

    } else {
      Swal.fire({
          title: 'Login Invalid',
          text: 'Username and Password required',
      });
    }
  }

  activityLog(activity, userID, fullName, bankCode, accessToken, bankName) {
    this.datalog = {
        application_name: 'Bank CRM',
      activity_name: activity,
      username: userID,
      full_name: fullName,
      access_token: accessToken,
      bank_name: bankName,
      bank_code: bankCode,
        activity_link: '',
        ip_address: ''
    };
    this.api.postActivityLog(this.datalog).subscribe(
      (res: any) => {
        this.error = false;
        this.message = res.message;
      },
        error => {
        this.error = true;
          this.message =
            "Failed to post activity log.";
        console.log("There's an error: " + JSON.stringify(error));
      }
    );
  }
}
