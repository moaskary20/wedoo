"use strict";
function category_query_params(p) {
  return {
    search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
  };
}
function address_query_params(p) {
  return {
    search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
  };
}
function all_settlement_cashcollection_queryParam(p) {
  return {
    search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
  };
}
function cash_collection_history_query_params(p) {
  return {
    search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
    cash_collection_filter: cash_collection_filter,
  };
}
function cash_collection_query_paramas(p) {
    return {
        search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
    };
}
function comission_history_query_params(p) {
    return {
        search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
    };
}
function country_code_query_params(p) {
    return {
        search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
    };
}
function faqs_query_params(p) {
    return {
        search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
    };
}
function manage_commission_query_params(p) {
    return {
        search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
    };
}
function notification_query_params(p) {
    return {
        search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
    };
}
function system_tax_query_params(p) {
    return {
        search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
    };
}
function system_user_query_params(p) {
    return {
        search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
    };
}
function email_query_param(p) {
  return {
      search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
      limit: p.limit,
      sort: p.sort,
      order: p.order,
      offset: p.offset,
  };
}
function sms_query_params(p) {
  return {
    search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
  };
}