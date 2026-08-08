<template>
    <div
        id="cover"
        v-show="url"
    >
        <img :src="url" alt="Cover not found"/>
    </div>
</template>

<script>
export default {
    name: "ProductsCover",
    data() {
        return {
            url: null,
        };
    },

    mounted() {
        document.addEventListener("mouseover", this.handleMouseOver);
        document.addEventListener("mouseout", this.handleMouseOut);
    },

    unmounted() {
        document.removeEventListener("mouseover", this.handleMouseOver);
        document.removeEventListener("mouseout", this.handleMouseOut);
    },

    methods: {
        handleMouseOver(e) {
            const coverUrl = e.target.dataset.coverUrl;
            if (coverUrl) {
                this.url = coverUrl;
            }
        },

        handleMouseOut(e) {
            if (e.target.dataset.coverUrl) {
                this.url = null;
            }
        }
    }
};
</script>

<style lang="scss">
#cover {
    position: fixed;
    right: 0;
    top: 58px;
    z-index: 10000;
    background-color: #fff;
    border: 1px solid #ccc;
    padding: 5px;

    img {
        vertical-align: top;
        max-height: 500px;
    }
}

@media screen and (max-width: 1500px) {
    #cover {
        img {
            max-height: 300px;
        }
    }
}
</style>
