---
title: Vuex store with normalized data using a Laravel backend
date: 2019-02-01
summary: State management in Vue can be done using just an object as a store, but when you're building a bigger application Vuex is a more mature solution. Using normalized data will make it a lot easier. I'll try and give you a sense of how to get started.
---

At the time of writing I'm working on a small SPA (Single Page Application) using Vue and Laravel. We've done some in-website-spa's in the past, for example a multi step form, but never an SPA by itself. The multi step forms usually had it's data passed as a prop or as static data inside the components itself. Mutations to the state were simply stored in an object that was shared across multiple components. Nothing wrong with that, but for bigger SPA's it might become a mess.

So it was time to check out [Vuex](https://vuex.vuejs.org/), which is the goto state management pattern and library for Vue.js applications. I looked into Vuex a few times, but I did not like the amount of files/boilerplate/etc. you have to create in order to setup something simple. However working on an SPA, I know that just using an object as a store is not going to work forever. So we settled for Vuex.

## Starting without a store

Basically you simply want some data in JSON format, and that's it right? Well, that only works up to a certain level. Let's assume we have a `Customer` model and a `CustomerController` controller. To get a list of customers we have in our database, we can get away with just the following:

```
<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function index() 
    {
        return Customer::all();
    }
}
```

When returning a model from a controller, or a collection of models as in this case; it will be [serialized to JSON](https://laravel.com/docs/5.7/eloquent-serialization#serializing-models-and-collections). The response data might look like this:

```
[
    {
        id: 1,
        name: 'Customer name'
    },
    {
        id: 2,
        name: 'Another customer'
    }
]
```

Assuming you're working with the basic preset that Laravel comes with, you have Vue and Axios already setup. Creating a component that can request the customer data and displays it is not that hard:

```
<template>
    <div>
        <div v-for="customer in customers" :key=customer.id>{{ customer.name }}</div>
    </div>
</template>

<script>
    export default {

        data() {
            return {
                customers: [],
            };
        },
        
        created() {
            axios
                .get('api/customers')
                .then((response) => { this.customers = response.data; });    
        },
    }
</script>
```

That would work, even if this component would be the customers list page in our SPA. For another page, we could simply request data from a different endpoint and life is good.

## Adding a store based on an object

In the example above the customers are stored in the component itself. How to get the customers from our Laravel backend is also known to this component. The first could be problematic when we need state to persist throughout navigating our SPA and the latter might be an issue when our endpoint changes.

Adding an object that will act as store for our customer state will help to solve some issues, imagine a `store/customer.js` file like this:

```
export default {
    
    state: {
        customers: [],
    },
    
    init() {
        axios
            .get('api/customers')
            .then((response) => { this.state.customers = response.data; });
    },
}
```

We can now change our component to get the customers from the store and telling the store to initalize, instead of doing all that itself.

```
<script>
    import customerStore from 'store/customer';

    export default {
    
        computed: {
            customers() {
                return customerStore.state.customers;
            },
        },
        
        created() {
            customerStore.init();
        },
    }
</script>
```

## Vuex

The next step is to upgrade this to use Vuex. Our previous solution allowed for a store per file, Vuex does this using [modules](https://vuex.vuejs.org/guide/modules.html). Be sure to check the 'Core concepts' section in the Vuex documentation, but I'll try and summarize it for you:

**State:** The single source of truth for your application.

**Getters:** An accessor for data derived from the state, for example `getCustomerById`.

**Mutations:** The one and only way to actually change the state, for example `setCustomers`.

**Actions:** The thing getting data from an API and calling mutations, for example `fetchCustomers`.

Create an `store/index.js` file looking like this:

```
import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

import customer from './customer';

export default new Vuex.Store({
    strict: true,
    modules: {
        customer,
    },
});
```

The `strict` parameter [prevents you from mutating the state directly](https://vuex.vuejs.org/guide/strict.html), so you can only mutate the state using mutations.

We're importing the customer store, which we change into the following:

```
import Vue from "vue";

export default {

    state: {
        customers: [],
    },

    getters: {
    
        getCustomerById: state => id => {
            return state.customers.find(customer => customer.id === id)
        },
    },

    actions: {

        fetchCustomers({ commit }) {
            return axios.get('api/customers')
                .then(response => {
                    commit('setCustomers', response.data);
                });
        },
    },

    mutations: {

        setCustomers(state, customers) {
            state.customers = customers;
        },
    },
};
```

It does look pretty right? Imagine a few more methods, for creating new customers, updating and deleting for example. That would be nicely structured and all in one place, instead of all over the place.

Our customer component should be changed accordingly:

```
<template>
    <div>
        <div v-if="loading">Fething customers...</div>
        <div v-else v-for="customer in customers" :key=customer.id>{{ customer.name }}</div>
    </div>
</template>

<script>
    import { mapState } from 'vuex';

    import store from 'store/';

    export default {
    
        data() {
            return {
                loading: true;
            };
        },
    
        computed: {
            ...mapState({
                customers: state => state.customer.customers,
            }),
        },

        created() {
            store.dispatch('fetchCustomers').then(() => { this.loading = false; });
        },
    }
</script>
```

The `mapState` component is one of the [helpers](https://vuex.vuejs.org/api/#component-binding-helpers) you can use to add stuff from your store to a component. We'll use it to map the customers state as a computed property.

To display a list of customers, we [dispatch](https://vuex.vuejs.org/api/#dispatch) an action to the store. In turn the `fetchCustomers` action will make a request to the `api/customers` endpoint to fetch the customers from our Laravel API. When the response is ready, the `setCustomer` mutation is actually persisting the customers to state. Which brings us back to the computed property in our component that will be able to provide the customer records to our template.

## Relations

This is when it gets interesting! We'll see a number of issues occurring when we add relations to Vuex. A customer can have many invoices, so lets create an `Invoice` model with the appropriate relations to the customer. Of course you will have to create the migration and run it, but that should not be an issue. In our customer controller we'd change the `return Customer::all();` to include invoices: `return Customer::with('invoices')->get();`. This will return something like this:

```
[
    {
        id: 1,
        name: 'Customer name',
        invoices: [
            {
                id: 1,
                description: 'Invoice description',
            },
            {
                id: 2,
                description: 'Another invoice',
            }
        ],
    },
    {
        id: 2,
        name: 'Another customer',
        invoices: []
    }
]
```

If we put the data in the customers store like this, we will run into an issue when we try to delete an invoice: `deleteInvoice(customer_id, invoice_id)`. We would have to loop through the customers array, find the customer with a specific ID and do that again for finding the invoice to delete within the invoices relation. Or we do `deleteInvoice(invoice_id)` and have to loop through every customer looking for the right customer ID. Imagine adding invoice rows and then updating or deleting one...

You might want to get a list of invoices, with the customer relation. The response data for a controller that makes that happen could look like this:

```
[
    {
        id: 1,
        description: 'Invoice description',
        customer: {
            id: 1,
            name: 'Customer name',
        },
    },
    {
        id: 2,
        description: 'Another invoice',
        customer: {
            id: 1,
            name: 'Customer name',
        },
    }
]
```

This highlights duplication; both invoices have the same customer data and multiple instances of the customer exists. Besides that, customer data would reside in the invoice store, like in the example above invoice data exists in the customer store. If you would update customer data, it would require you to do that in all invoices.

## Normalizr to the rescue

After struggling with the above for a while, I found a solution! A package named [paularmstrong/normalizr](https://github.com/paularmstrong/normalizr) came to the rescue. Instead of throwing the response data into the stores as is, it normalizes it into objects. So our stores states would look like this:

```
{
    state: {
        customers: {
            1: {
                id: 1,
                name: 'Customer name',
                invoices: [1, 2]
            },
            2: {
                id: 2,
                name: 'Another customer',
                invoices: []
            }
        }
    }
}
```

```
{
    state: {
        invoices: {
            1: {
                id: 1,
                description: 'Invoice description',
                customer: 1,
            },
            2: {
                id: 2,
                description: 'Another invoice',
                customer: 1,
            }
        }
    }
}
```

Mind blowing, right!

You can reference anything right through the key. No more looping through customers to find an invoice and no more duplicates either. However to get to the above we have to create schema's, create stubs and some other stuff.

Our `fetchCustomers` will have to use a schema to normalize the response data. It's not necessary to add any other attributes, just the relations will do fine. In the case of the customers schema, there is an invoices relation: 

```
import { schema } from 'normalizr';

const customerSchema = new schema.Entity('customers');
const invoiceSchema = new schema.Entity('invoices');

customerSchema.define({
    invoices: [invoiceSchema]
});
```

The `normalize` method will output entities. When fetching customers, we'll have `customers` and `invoices` as entities. We have to set both, so there should be an invoice store too, a complete example will follow. Be sure to import `normalize` from `normalizr` for use here:

```
fetchCustomers({ commit }) {
    return axios.get('api/customers')
          .then(response => {
              const { entities } = normalize(response.data, [customerSchema]);
              commit('setCustomers', entities.customers);
              commit('setInvoices', entities.invoices);
          });
},
```

Our `setCustomers` mutation also needs to be changed. Instead of simply replacing the whole state with the new array, we now loop over the entities given by normalizr and apply the new data to it. This way, for any mutations we have for a customer we can simply use `setCustomers` instead of having to create new mutations for every thing we do. In my case I will rely on the server to provide new data, after updating for example, so everything will be normalized and persisted to the store like this.

```
setCustomers(state, customers) {

    for (let customer in customers) {

        // Get existing object from state, or create an empty object based on a stub
        const oldObj = state.customers[customer] || Object.assign({}, customerStub);

        // Merge the new data into the old object.
        const newObj = Object.assign(oldObj, customers[customer]);

        // Set new object in state.
        Vue.set(state.customers, customer, newObj);
    }
},
```

The `customerStub` is needed to make sure Vue binds it reactive hooks to the invoices relation. If you don't include that, adding invoices will not be noticed by Vue and they will not show up.

```
const customerStub = {
    invoices: []
};
```

Fun thing is that it will work exactly the same for invoices. The only thing that really changes is the schema. Entities parsed using the schema might have different attributes, but only the relations are referenced in the schema.

## Putting things together

It would be bad to leave things like this, so let's put something together which incorporates all this. We'll have two components; one lists all customers and one lists all invoices. Using [Vue Router](https://router.vuejs.org/) would allow you to really create an SPA that makes using stores worthwile.

### Customers

Our customer component:

```
<template>
    <div>
        <div v-if="loading">Fething customers...</div>
        <div v-else v-for="customer in customers" :key=customer.id>
            {{ customer.name }}
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex';

    import store from 'store/';

    export default {
    
        data() {
            return {
                loading: true;
            };
        },
    
        computed: {
            ...mapState({
                customers: state => state.customer.customers,
            }),
        },

        created() {
            store.dispatch('fetchCustomers').then(() => { this.loading = false; });
        },
    }
</script>
```

Our customer store:

```
import Vue from "vue";
import { schema } from 'normalizr';

const customerSchema = new schema.Entity('customers');
const invoiceSchema = new schema.Entity('invoices');

customerSchema.define({
    invoices: [invoiceSchema]
});

const customerStub = {
    invoices: []
};

export default {

    state: {
        customers: [],
    },

    getters: {
    
        getCustomerById: state => id => {
            return state.customers[Number(id)];
        },
    },

    actions: {

        fetchCustomers({ commit }) {
            return axios.get('api/customers')
                .then(response => {
                    const { entities } = normalize(response.data.data, [customerSchema]);
                    commit('setCustomers', entities.customers);
                    commit('setInvoices', entities.invoices);
                });
        },
    },

    mutations: {

        setCustomers(state, customers) {
        
            for (let customer in customers) {
        
                // Get existing object from state, or create an empty object based on a stub
                const oldObj = state.customers[customer] || Object.assign({}, customerStub);
        
                // Merge the new data into the old object.
                const newObj = Object.assign(oldObj, customers[customer]);
        
                // Set new object in state.
                Vue.set(state.customers, customer, newObj);
            }
        },
    },
};
```

### Invoices

Our invoice component:

```
<template>
    <div>
        <div v-if="loading">Fething invoices...</div>
        <div v-else v-for="invoice in invoices" :key=invoice.id>
            {{ customers[invoice.customer].name }}: {{ invoice.description }}
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex';

    import store from 'store/';

    export default {
    
        data() {
            return {
                loading: true;
            };
        },
    
        computed: {
            ...mapState({
                customers: state => state.customer.customers,
                invoices: state => state.invoice.invoices,
            }),
        },

        created() {
            store.dispatch('fetchInvoices').then(() => { this.loading = false; });
        },
    }
</script>
```

Our invoice store:

```
import Vue from "vue";
import { schema } from 'normalizr';

const invoiceSchema = new schema.Entity('invoices');
const customerSchema = new schema.Entity('customers');

invoiceSchema.define({
    customer: customerSchema
});

const invoiceStub = {
    customer: null
};

export default {

    state: {
        invoices: [],
    },

    getters: {
    
        getInvoiceById: state => id => {
            return state.invoices[Number(id)];
        },
    },

    actions: {

        fetchInvoices({ commit }) {
            return axios.get('api/invoices')
                .then(response => {
                    const { entities } = normalize(response.data.data, [invoiceSchema]);
                    commit('setInvoices', entities.invoices);
                    commit('setCustomers', entities.customers);
                });
        },
    },

    mutations: {

        setInvoices(state, invoices) {
        
            for (let invoice in invoices) {
        
                // Get existing object from state, or create an empty object based on a stub
                const oldObj = state.invoices[invoice] || Object.assign({}, invoiceStub);
        
                // Merge the new data into the old object.
                const newObj = Object.assign(oldObj, invoices[invoice]);
        
                // Set new object in state.
                Vue.set(state.invoices, invoice, newObj);
            }
        },
    },
};
```

## Small benefits

From the code above we can quickly spot some nice uses of the normalized data:

```
getCustomerById: state => id => {
    return state.customers[Number(id)];
},
```

^ Our customer store: No more looping through all customers to find one. Especially for big collections of customers, this is a lot faster.

---

```
customers[invoice.customer].name
```

^ Our invoice component: Simply add the customers as computed property from the state and since `invoice.customer` is the customer's ID we can reference the customer directly. Otherwise we'd need a method to find the customer, looping through all customers again.

---

```
setCustomers(state, customers) { /***/ }
```

^ In our customer store: It's very likely that you don't want to load all invoices and their rows (for example) for simply showing a list of customers. With normalized results it doesn't matter if you include them or not, both will work.

Imagine listing all customers and not including the invoices relation, then clicking a customer showing a modal with list of customers on the background. When opening the modal, you fetch one customer with the invoices relation. The same `setCustomers` mutation will be used simply expanding on what was already in the state.

## In closing

There is many more to it then what I described here. I barely touched the surface here, but it should get you started. We did a lot on the server side to allow for dynamically loading relations, so we can use the same endpoints for multiple purposes. Mainly [Eloquent Resources](https://laravel.com/docs/5.7/eloquent-resources) and [spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder) made that very easy.

Maybe there is a better way to things, would love to hear your ideas. Find me on Twitter!
