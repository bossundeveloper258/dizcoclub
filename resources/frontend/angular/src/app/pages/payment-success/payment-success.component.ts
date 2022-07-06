import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-payment-success',
  templateUrl: './payment-success.component.html',
  styleUrls: ['./payment-success.component.css']
})
export class PaymentSuccessComponent implements OnInit {
  routeAsset = environment.assetsUrl;
  token!: string;
  constructor(
    private activatedRoute: ActivatedRoute,
  ) { 
    this.activatedRoute.paramMap.subscribe( paramMap => {
      this.token = paramMap.get('id') ?? "";
    });
  }

  ngOnInit(): void {
  }

}
