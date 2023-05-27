import { Component, OnInit, Input } from "@angular/core";
import Swal from "sweetalert2";
import { Router } from "@angular/router";

@Component({
  selector: "app-header",
  templateUrl: "./header.component.html",
  styleUrls: ["./header.component.css"],
})
export class HeaderComponent implements OnInit {
  @Input() public isLoading;
  public username: string;
  public bank_name: string;
  public full_name: string;
  public bank_code: string;
  public bank_logo: string;
  public is_gohalal: string;
  date = new Date();

  constructor(private route: Router) {}

  ngOnInit() {
    this.username = localStorage.getItem("user_id");
    this.bank_name = localStorage.getItem("bank_name");
    this.full_name = localStorage.getItem("full_name");
    this.bank_code = localStorage.getItem("bank_code");
    this.bank_logo = localStorage.getItem("bank_logo");
    this.is_gohalal = localStorage.getItem("is_gohalal");
  }

  onLogout() {
    Swal.fire({
      title: "Are you sure?",
      text: "You will logout",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#337ab7",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, Logout!",
    }).then((result) => {
      if (result.value) {
        localStorage.setItem("access_token", null);
        Swal.fire("Logout success!", "See you again!", "success");
        localStorage.clear();
        this.route.navigate(["/login"]);
      }
    });
  }
}
