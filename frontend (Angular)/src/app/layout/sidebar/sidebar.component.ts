import { Component, OnInit } from "@angular/core";
import Swal from "sweetalert2";
import { Router } from "@angular/router";
import { ApiService } from "src/app/api.service";

@Component({
  selector: "app-sidebar",
  templateUrl: "./sidebar.component.html",
  styleUrls: ["./sidebar.component.css"],
})
export class SidebarComponent implements OnInit {
  userId = localStorage.getItem("user_id");
  fullName = localStorage.getItem("full_name");
  bankCode = localStorage.getItem("bank_code");
  accessToken = localStorage.getItem("access_token");
  bankName = localStorage.getItem("bank_name");
  datalog = {};
  error;
  message;
  isGoHalal: boolean;
  bckgrnd: any;

  menuUser: any;
  showMenu: any;

  sideBarMenu = [
    {
      menuKey: "dashboard_menu",
      menuTitle: "Dashboard",
      menuUrl: "/home",
      showMenu: true,
      menuIcon: "fas fa-fw fa-tachometer-alt",
    },
  ];

  constructor(private route: Router, private api: ApiService) {
    // console.log('Sidebar constructor called');
  }

  ngOnInit() {
    // console.log(
    //   "from sidebar: " + atob(JSON.parse(localStorage.getItem("user_menu")))
    // );
    // console.log(this.showMenu.dashboard_menu);
    this.isGoHalal = false;
    this.bckgrnd = 'bg-gradient-primary';
    if (localStorage.getItem("is_gohalal") == "1") {
      this.isGoHalal = true;
      this.bckgrnd = 'bg-gradient-primary-ghp';
    }
    console.log('sini ghp = '+this.isGoHalal);

    if (
      !localStorage.hasOwnProperty("user_menu") ||
      localStorage.getItem("user_menu") === null
    ) {
      this.route.navigate(["/login"]);
    } else {
      this.menuUser = atob(localStorage.getItem("user_menu"));
      this.showMenu = JSON.parse(this.menuUser);
      this.sideBarMenu = [
        {
          menuKey: "dashboard_menu",
          menuTitle: "Dashboard",
          menuUrl: "/home",
          showMenu: this.showMenu.dashboard_menu,
          menuIcon: "fas fa-fw fa-tachometer-alt",
        },
        {
          menuKey: "cc_menu",
          menuTitle: "Credit Card",
          menuUrl: "/application-cc",
          showMenu: this.showMenu.cc_menu,
          menuIcon: "fas fa-fw fa-tachometer-alt",
        },
        {
          menuKey: "pf_menu",
          menuTitle: "PF Application",
          menuUrl: "/application",
          showMenu: this.showMenu.pf_menu,
          menuIcon: "fas fa-fw fa-pencil-alt",
        },
        // {
        //   menuKey: "fwd_menu",
        //   menuTitle: "FWD Reports",
        //   menuUrl: "/application-fwd",
        //   showMenu: this.showMenu.fwd_menu,
        //   menuIcon: "fas fa-fw fa-pencil-alt",
        // },
        {
          menuKey: "pf_product_menu",
          menuTitle: "PF Products",
          menuUrl: "/products",
          showMenu: this.showMenu.pf_product_menu,
          menuIcon: "fas fa-fw fa-folder",
        },
        {
          menuKey: "cc_product_menu",
          menuTitle: "CC Products",
          menuUrl: "/products-cc",
          showMenu: this.showMenu.cc_product_menu,
          menuIcon: "fas fa-fw fa-folder",
        },
        {
          menuKey: "analytics_menu",
          menuTitle: "Analysis",
          menuUrl: "/analysis",
          showMenu: this.showMenu.analytics_menu,
          menuIcon: "fas fa-fw fa-chart-line",
        },
        {
          menuKey: "assessment_menu",
          menuTitle: "Assesment",
          menuUrl: "/assestment",
          showMenu: this.showMenu.assessment_menu,
          menuIcon: "fas fa-fw fa-file-alt",
        },
        {
          menuKey: "settings_menu",
          menuTitle: "Setting",
          menuUrl: "/setting",
          showMenu: this.showMenu.settings_menu,
          menuIcon: "fas fa-fw fa-cog",
        },
      ];
    }
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
        this.activityLog(
          "logout",
          this.userId,
          this.fullName,
          this.bankCode,
          this.accessToken,
          this.bankName
        );
        localStorage.clear();
        this.route.navigate(["/login"]);
      }
    });
  }

  activityLog(activity, userID, fullName, bankCode, accessToken, bankName) {
    this.datalog = {
      application_name: "Bank CRM",
      activity_name: activity,
      username: userID,
      full_name: fullName,
      access_token: accessToken,
      bank_name: bankName,
      bank_code: bankCode,
      activity_link: "",
      ip_address: "",
    };
    this.api.postActivityLog(this.datalog).subscribe(
      (res: any) => {
        this.error = false;
        this.message = res.message;
      },
      (error) => {
        this.error = true;
        this.message = "Failed to post activity log.";
        console.log("There's an error: " + JSON.stringify(error));
      }
    );
  }
}
