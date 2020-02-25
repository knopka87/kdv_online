<?php

namespace app\models\kdv;


class KdvProduct
{
    private $kdv;

    public function __construct()
    {
        $this->kdv = Kdv::getInstance();
    }

    public function getProductList($categoryId, $page = 1) {

        $data = json_decode($this->kdv->curl(
            "{\"query\":\"  query (\$categoryId: Int!, \$filter: [FilterInput], \$sort: SortInput, \$pagination: PageInput) {\\r\\n".
            "    products (categoryId: \$categoryId, filter: \$filter, sort: \$sort, page: \$pagination) {\\r\\n".
            "      list {\\r\\n".
                    "  id\\r\\n".
                    "  name\\r\\n".
                    //"  description\\r\\n".
                    "  amount\\r\\n".
                    "  image {\\r\\n".
                        "  id\\r\\n".
                        "  title\\r\\n".
                        "  alt\\r\\n".
                        "}\\r\\n".
                    "  price\\r\\n".
                    //"  isNew\\r\\n".
                    //"  isHit\\r\\n".
                    //"  isFavorite\\r\\n".
                    "  isAvailable\\r\\n".
                    //"  isWeight\\r\\n".
                    //"  isSubscribed\\r\\n".
                    "  isVeterinaryControl\\r\\n".
                    "  code\\r\\n".
                    "  quant {\\r\\n".
                        "  code\\r\\n ".
                        "  fullName\\r\\n".
                        "  shortName\\r\\n".
                        "  multiple\\r\\n".
                        "  pricePerUnit\\r\\n".
                        "  unit\\r\\n".
                        "}\\r\\n".
                    "  categories {\\r\\n".
                        "  id\\r\\n".
                        "  name\\r\\n".
                        "  code\\r\\n".
                        "}\\r\\n".
                    "}\\r\\n".
            "      page {\\r\\n".
                    "  total\\r\\n".
                    "  limit\\r\\n".
                    "  page\\r\\n".
                    "}\\r\\n".
            /*"      filter {\\r\\n".
                    "  id\\r\\n".
                    "  type\\r\\n".
                    "  name\\r\\n".
                    "  title\\r\\n".
                    "  options {\\r\\n".
                        "  id\\r\\n".
                        "  value\\r\\n".
                        "  label\\r\\n".
                        "  count\\r\\n".
                        "  isApplied\\r\\n".
                        "}\\r\\n".
                    "}\\r\\n".*/
            "      sort {\\r\\n".
                    "  name\\r\\n".
                    "  title\\r\\n".
                    "  direct\\r\\n".
                    "  isApplied\\r\\n".
                    "}\\r\\n".
            "    }\\r\\n".
            "  }\",\"variables\":{\"categoryId\":$categoryId,\"pagination\":{\"page\":$page,\"limit\":100}}}"));
        return $data->data->products->list;
    }

    public function getProductInfo($productId)
    {
        $data = json_decode($this->kdv->curl(
            "{\"query\":\"query (\$id: Int!) {\\r\\n".
            "    product (id: \$id) {\\r\\n".
                    "  \\r\\n".
                    "  id\\r\\n".
                    "  name\\r\\n".
                    //"  description\\r\\n".
                    "  amount\\r\\n".
                    "  image {\\r\\n".
                        "  id\\r\\n".
                        "  title\\r\\n".
                        //"  alt\\r\\n".
                        "}\\r\\n".
                    "  price\\r\\n".
                    //"  isNew\\r\\n".
                    //"  isHit\\r\\n".
                    //"  isFavorite\\r\\n".
                    "  isAvailable\\r\\n".
                    //"  isWeight\\r\\n".
                    //"  isSubscribed\\r\\n".
                    "  isVeterinaryControl\\r\\n".
                    "  code\\r\\n".
                    "  quant {\\r\\n".
                        "  code\\r\\n".
                        "  fullName\\r\\n".
                        "  shortName\\r\\n".
                        "  multiple\\r\\n".
                        "  pricePerUnit\\r\\n".
                        "  unit\\r\\n".
                        "}\\r\\n".
                    "  categories {\\r\\n".
                        "  id\\r\\n".
                        "  name\\r\\n".
                        "  code\\r\\n".
                        "}\\r\\n".
                    "\\r\\n".
                    "  images {\\r\\n".
                        "  id\\r\\n".
                        "  title\\r\\n".
                        //"  alt\\r\\n".
                        "}\\r\\n".
                    "  properties {\\r\\n".
                    "  name\\r\\n".
                    "  title\\r\\n".
                    //"  type\\r\\n".
                    "  unit\\r\\n".
                    "  value\\r\\n".
                    //"  label\\r\\n".
                    //"  multiple\\r\\n".
                    /*"  group {\\r\\n".
                        "  id\\r\\n".
                        "  title\\r\\n".
                        "}\\r\\n".*/
                    "}\\r\\n".
                "}\\r\\n".
            "  }\",\"variables\":{\"id\":$productId}}"));

        return $data->data->product;
    }

}
