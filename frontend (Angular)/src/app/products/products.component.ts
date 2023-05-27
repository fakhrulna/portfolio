import { Component, OnInit } from "@angular/core";
import { ApiService } from "../api.service";

@Component({
  selector: "app-products",
  templateUrl: "./products.component.html",
  styleUrls: ["./products.component.css"],
})
export class ProductsComponent implements OnInit {
  products: any = [];
  isLoaded = false;

  constructor(private apiService: ApiService) {}

  ngOnInit() {
    const data = {
      token: localStorage.getItem("access_token"),
      status: true,
    };
    // this.products = [];
    // for (const key in localStorage) {
    //   data.push(key);
    // }

    this.apiService.listProduct(data).subscribe((response) => {
      this.isLoaded = true;
      this.products = response.data.products;
    });
  }
}
