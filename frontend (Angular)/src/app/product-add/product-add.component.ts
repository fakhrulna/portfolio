import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-product-add',
  templateUrl: './product-add.component.html',
  styleUrls: ['./product-add.component.css']
})
export class ProductAddComponent implements OnInit {
  bankList: any

  constructor() { }

  ngOnInit() {
    this.bankList = [
      {'bank_name': 'Agro'},
      {'bank_name': 'RHB'}
    ]
    console.log(this.bankList);
  }

}
