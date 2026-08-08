<template>
    <div class="card mb-5">
        <div class="card-body p-5 pb-0">
            <div class="col-md-6 p-0 m-0">
                <div class="input-group tag-add-control">
                    <span class="input-group-text"><span class="fas fa-tags"></span></span>
                    <select
                        v-model="selectedTag"
                        class="form-select"
                    >
                        <option
                            v-for="tag in tags"
                            :key="tag.id"
                            :value="tag"
                        >
                            {{ tag.title }}
                        </option>
                    </select>
                    <button
                        class="btn btn-primary"
                        type="button"
                        title="Add tag"
                        @click="addTag"
                    >
                        <span class="fas fa-plus-circle"></span>
                    </button>
                </div>
            </div>
            <div class="spacer-10"></div>
            <ul class="tags-list">
                <li
                    v-for="tag in localEntityTags"
                    :key="tag.id"
                >
                    <span
                        class="f14 badge"
                        :class="`bg-${tag.color}`"
                    >
                        {{ tag.title }}
                    </span>
                    <input
                        type="hidden"
                        name="tag_ids[]"
                        :value="tag.id"
                    />
                    <span
                        class="fas fa-xmark"
                        title="Remove tag"
                        @click="removeTag(tag)"
                    ></span>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    name: "EntityTagsEditor",

    props: {
        entityTags: {
            type: Array,
            default: () => []
        },
        tags: {
            type: Array,
            default: () => []
        }
    },

    data() {
        return {
            selectedTag: null,
            localEntityTags: []
        };
    },

    mounted() {
        this.selectedTag = this.tags[0];
        // Initialize local copy of entity tags
        this.localEntityTags = [...this.entityTags];
    },

    methods: {
        addTag() {
            if (!this.selectedTag) {
                return;
            }

            // Check if tag is already added
            const existingTag = this.localEntityTags.find(tag => tag.id === this.selectedTag.id);
            if (existingTag) {
                // tag was already added
                return;
            }

            // Add to local array (Vue 3 reactive)
            this.localEntityTags.push(this.selectedTag);
        },

        removeTag(tag) {
            const index = this.localEntityTags.findIndex(item => item.id === tag.id);
            if (index !== -1) {
                // Remove from local array (Vue 3 reactive)
                this.localEntityTags.splice(index, 1);
            }
        }
    }
};
</script>
